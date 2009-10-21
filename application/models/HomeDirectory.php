<?php
class Model_HomeDirectory()
{
    $config = Config::getInstance();
    $username = Auth::getInstance->getUsername();

    if (empty($config->manualHomeDir['overrideAuto'])) {
        $userInfo = posix_getpwnam($username);

        if ( ! empty($userInfo['dir']) && is_dir($userInfo['dir'])) {
            return $userInfo['dir'];
        }
    }

    preg_match($config->manualHomeDir['userRegEx'], $username, $userNameParts);
    $dir = str_replace('%', $userNameParts, $config->manualHomeDir['path']);

    if (empty($userInfo['dir']) || ! is_dir($userInfo['dir'])) {
        //TODO: Throw exception
        exit('Home directory was not set');
    }

	return $dir;
}
