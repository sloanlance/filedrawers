<?php
class Helper_CreateLinkPath extends View_Helper
{
    public function createLinkPath()
    {
        $pathHTML = '';
        $path = $this->view->baseUrl() . '/list';
        $parts = explode('/', $this->view->path);
        $partsLen = count($parts);

        for($i=0; $i<$partsLen; $i++) {
            $path .= $parts[$i] . '/';

            if ($i == ($partsLen - 1)) {
                $pathHTML .= "<span>" . $parts[$i] . "</span>";
            }
            else{
                $pathHTML .= "<a href=\"$path\">" . $parts[$i] . "</a>/";
            }
        }

        return $pathHTML;
    }
}

