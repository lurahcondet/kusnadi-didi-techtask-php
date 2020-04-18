<?php

/**
 * @author Didi Kusnadi <jalapro08@gmail.com>
 */

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    /**
     * Handling error response
     * @param  ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
    	$exception = $event->getThrowable();

    	$responseCode = 404;
    	if($exception instanceof \ErrorException){
    		$responseCode = 500;
    	}

        $customResponse = new JsonResponse(
        	[
        		'status' => false, 
        		'message' => $exception->getMessage()
        	], 
        	$responseCode
        );
        
        $event->setResponse($customResponse);

    }
}