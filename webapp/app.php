<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app->before(function() use ($app) {
    $app['config.locale'] = $app['request']->attributes->get('_locale');
    $app['globaldata'] = array();
});

$app->get('/', function() use($app) {
	if($app['config.redirect_home']) {
		return $app->redirect($app['config.home']);
	} else {
		$qstring = $app['request']->getQueryString();
		$params = '?redirect=false';
		if (!empty($qstring)) {
			$params .= '&' . $qstring;
		}
		$subRequest = Request::create('/' . $app['config.home'] . '/' . $params, 'GET', array(), $app['request']->cookies->all(), array(), $app['request']->server->all());
		$response = $app->handle($subRequest, HttpKernelInterface::MASTER_REQUEST, false);
		return $response;
	}
});

$app->get('/clear_cache', function() use($app) {
	$app['storyblok']->deleteCacheBySlug($app['request']->get('slug'));

	if ($app['request']->get('action') == 'published') {
		// Fill the cache immediatly after publishing the story
		$app['storyblok']->getStoryBySlug($app['request']->get('slug'));
	}

	if(empty($app['request']->get('slug'))) {
		$app['storyblok']->flushCache();
	}

	return $app->json(array('success' => true));
});

$app->mount('/{slug}', new App\Controllers\Web());

$app->run();
