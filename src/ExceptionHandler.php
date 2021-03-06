<?php namespace WhiteFrame\Http;

use Exception;
use WhiteFrame\Http\Response\AjaxResponse;

/**
 * Class ExceptionHandler
 * @package WhiteFrame\Http
 */
class ExceptionHandler extends \App\Exceptions\Handler
{
	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
		if($request->ajax()) {
			$response = new AjaxResponse($request);
			$response->status(500, 'error', $e->getMessage(), ['exception' => class_basename($e)]);
			return $response->get();
		}
		elseif($request->has('callback')) {
			$response = new AjaxResponse($request);
			$response->status(200, 'error', $e->getMessage(), ['exception' => class_basename($e)]);
			return $response->get();
		}
		else {
			return parent::render($request, $e);
		}
	}

	public function getAjaxMessage(Exception $e)
	{
		$message = class_basename($e);

		if(!empty($e->getMessage())) {
			$message .= ' - ' . $e->getMessage();
		}

		return $message;
	}
}
