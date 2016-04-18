<?php namespace WhiteFrame\Http\Response;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use WhiteFrame\Http\Contracts\Model;
use WhiteFrame\Http\Contracts\ResponseType;

/**
 * Class ResponseFactory
 * @package WhiteFrame\Http\Response
 */
class ResponseFactory extends Response
{
	protected $request;
	protected $fractal;
	protected $types;

	public function __construct(Request $request)
	{
		parent::__construct();

		$this->request = $request;
		$this->fractal = new FractalManager();
		$this->types = Collection::make([
			'ajax' => new AjaxResponse($request),
			'browser' => new BrowserResponse($request)
		]);
	}

	/**
	 * @param null $message
	 */
	public function success($message = null)
	{
		$this->status(self::HTTP_OK, 'success', $message);

		return $this;
	}

	/**
	 * @param null $message
	 */
	public function fail($message = null)
	{
		$this->status(self::HTTP_BAD_REQUEST, 'fail', $message);

		return $this;
	}

	/**
	 * @param null $message
	 */
	public function error($message = null)
	{
		$this->status(self::HTTP_INTERNAL_SERVER_ERROR, 'error', $message);

		return $this;
	}

	/**
	 * @param Model $model
	 */
	public function item(Model $model)
	{
		if($model->hasTransformer()) {
			$resource = new FractalItem($model, $model->getTransformer());
			$datas = $this->fractal->createData($resource)->toArray();
		}
		else {
			$datas = $model->toArray();
		}

		$this->types->get('ajax')->datas($datas);

		return $this;
	}

	/**
	 * @param EloquentCollection $models
	 */
	public function items(EloquentCollection $models)
	{
		if($models->first()->hasTransformer()) {
			$resource = new FractalCollection($models, $models->first()->getTransformer());
			$datas = $this->fractal->createData($resource)->toArray();
		}
		else {
			$datas = $models->toArray();
		}

		$this->types->get('ajax')->datas($datas);

		return $this;
	}

	public function view($view, $params = [])
	{
		$this->types->get('browser')->view($view, $params);

		return $this;
	}

	public function redirect($url = null)
	{
		return $this->types->get('browser')->redirect($url);
	}

	/**
	 *
	 */
	public function send()
	{
		if($this->request->ajax()) {
			$this->types->get('ajax')->get()->send();
		}
		else {
			$this->types->get('browser')->get()->send();
		}
	}

	protected function status($code = 200, $status = null, $message = null)
	{
		$this->types->map(function(ResponseType $type) use ($code, $status, $message) {
			$type->status($code, $status, $message);
		});
	}
}