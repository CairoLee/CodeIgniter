<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends Lee_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}

    public function smarty()
    {
        // 根据环境，决定是否显示 CI 版本信息
        $ci_version = (ENVIRONMENT === 'development') ? 'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '';

        $this->assign('ci_version', $ci_version);
        $this->assign('data', array(
            'title' => 'Welcome to CodeIgniter',
            'h1' => 'Welcome to CodeIgniter - Smarty !'
        ));
        $this->display('welcome_message');
    }
}
