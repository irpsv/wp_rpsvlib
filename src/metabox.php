<?php

namespace FCL;

class Metabox
{
    const CONTEXT_NORMAL    = 'normal';
    const CONTEXT_SIDE      = 'side';
    const CONTEXT_ADVANCED  = 'advanced';
    
    const PRIORITY_HIGH     = 'high';
    const PRIORITY_LOW      = 'low';
    
    public $id; // required
    public $title; // required
    public $screen;
    public $context = self::CONTEXT_ADVANCED;
    public $priority = 'default';
    public $callback; // required
    public $callback_args;
    //
    public $post_type;
    public $saveCallback; // required
    
    public function __construct($post_type) {
        $this->post_type = $post_type;
        $hook = 'add_meta_boxes_'.$this->post_type;
        add_action($hook,[$this,'register']);
        add_action('save_post',[$this,'save'], 10, 3);
    }
    
    public function register() {
        add_meta_box(
            $this->id, 
            $this->title, 
            $this->callback, 
            $this->screen, 
            $this->context, 
            $this->priority, 
            $this->callback_args
        );
    }
    
    public function setRenderFile($filepath) {
        $this->renderFile($filepath);
    }
    
    public function renderFile($filepath) {
        $this->callback = function() use ($filepath) {
            include $filepath;
        };
    }
    
    public function save($id_post, $post, $isUpdate) {
        call_user_func_array(
            $this->saveCallback, 
            array($id_post,$post,$isUpdate)
        );
    }
}