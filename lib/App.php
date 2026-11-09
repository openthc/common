<?php
/**
 * A Big Wrapper for Slim to make Apps "our" way
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC;

class App
{
	static function factory() : \Slim\Factory\AppFactory
	{
		$dic = new \DI\Container();
		\Slim\Factory\AppFactory::setContainer($dic);
		\Slim\Factory\AppFactory::setResponseFactory(new \OpenTHC\HTTP\Response\Factory());
		$app = \Slim\Factory\AppFactory::create();

		// Add Error Handler
		$errorMiddleware = $app->addErrorMiddleware(
			displayErrorDetails: true,
			logErrors: true,
			logErrorDetails: true
		);
		$errorMiddleware->setDefaultErrorHandler(function (
			ServerRequestInterface $request,
			Throwable $exception,
			bool $displayErrorDetails
		) use ($app) {
			$response = $app->getResponseFactory()->createResponse(500);
			$response->getBody()->write($exception->getMessage());
			if ($displayErrorDetails) {
				$response->getBody()->write("\n----\n");
				$response->getBody()->write($exception->getTraceAsString());
				// $p = $exception->getPrevious();
			}
			return $response->withHeader('content-type', 'text/plain');
		});

		return $app;
	}
}
