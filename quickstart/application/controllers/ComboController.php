<?php

class ComboController extends StaticController {

    public function init() {
    }

    // usage: http://host/api/combo?f=file1;file2;file3
    public function indexAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $combo_file = '';
        if(empty($_REQUEST['f'])) {
            echo $combo_file;
            return;
        }
        $cwd = getcwd();
        $files = explode(';', $_REQUEST['f']);
        foreach($files as $file)
        {
            if(strpos("..", $file)) continue;
            if(!preg_match("/\.(js|css|html)$/", $file)) continue;
            $abs_path = $cwd . "/" . $file;
            if(!file_exists($abs_path))continue;
            $combo_file .= file_get_contents($abs_path);
        }
        echo $combo_file;
    }
}
