<?php

namespace werx\Core\Modules;

use werx\Core\Module;
use werx\Core\WerxApp;
use Symfony\Component\HttpFoundation\Session\Session;

class NativeSession extends Module
{
	public function config(WerxApp $app)
	{
		/**
		 * Setup the session with cookie expiration of one week. This will
		 * allow the session to persist even if the browser window is closed.
		 * The session expiration will still be respected (default 1 hour).
		 */
		$services = $app->getServices();
		$services->setSingleton('session', function() {
			$session = new Session(
				new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(
					['cookie_lifetime' => 604800]
				)
			);
			$session->setName("WERXAPP_" . sha1($app->getName()));
			return $session;
		});
	}

	public function handle(WerxApp $app)
	{
		$this->start($app->session, $app->config->get('session_expiration', 3600));
		$this->handleNext($app);
		$this->save($app->session);
	}

	public function start($session, $expires)
	{
		try {
			$session->start();
			if (time() - $session->getMetadataBag()->getLastUsed() > $expires) {
				$session->invalidate();
				// @todo: signal app the session was invalidated.
			}
		}
		catch (\LogicException $e) {
			// Session already active, can't change it now!
		}
		catch (\Exception $e) {
			// Something else bad happend;
			// @todo: show/log this?
		}

	}

	public function save($session)
	{
	}
}
