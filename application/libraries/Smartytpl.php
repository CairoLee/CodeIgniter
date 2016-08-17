<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Smartytpl extends Smarty {

    protected $CI;
    protected $template_ext;

    public function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
        $this->CI->load->config('smarty');

        // 读取并加载配置
        $this->template_dir   = $this->CI->config->item('template_dir');
        $this->compile_dir    = $this->CI->config->item('compile_dir');
        $this->cache_dir      = $this->CI->config->item('cache_dir');
        $this->config_dir     = $this->CI->config->item('config_dir');
        $this->template_ext   = $this->CI->config->item('template_ext');

        $this->addPluginsDir($this->CI->config->item('plugins_dir'));

        $this->assign('ci_env', ENVIRONMENT);
        $this->assign('elapsed_time', $this->CI->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end'));
        $this->assign('memory_usage', ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage() / 1024 / 1024, 2) . 'MB');
    }

    public function display($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL)
    {
        parent::display($template . $this->template_ext, $cache_id, $compile_id, $parent);
    }

    public function fetch($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL)
    {
        return parent::fetch($template . $this->template_ext, $cache_id, $compile_id, $parent);
    }
}
