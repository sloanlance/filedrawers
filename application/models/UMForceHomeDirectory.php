<?php
class Model_UMForceHomeDirectory
{
    public function getHomeDirectory(&$homedir)
    {
        $config = Zend_Registry::get('config');
        $userInfo = posix_getpwnam(Zend_Auth::getInstance()->getIdentity());

        if ( ! empty($userInfo['dir']) && is_dir($userInfo['dir'])) {
            $homedir = $userInfo['dir'];
        }

        $forceAfsUserDir = $config->filesystem->services->afs->forceAfsUserDir;

        if (strpos($homedir, '/home') === 0) {
            $userSuffix = '/' . $userInfo['name'][0] . '/'.$userInfo['name'][1] . '/' . $userInfo['name'];
            $homedir = $forceAfsUserDir . $userSuffix;
        }
    }
}

