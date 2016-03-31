<?php

namespace FCL;

class Tax
{
    /**
     * Название таксономии
     * @var string
     */
    public $taxonomy;
    /**
     * Название типа объекта таксономии
     * @var string|array
     */
    public $object_type;
    /**
     * Заголовок
     * @var string
     */
    public $label;
    /**
     * Заголовоки
     * @var array
     */
    public $labels;
    /**
     * Краткое описание таксономии
     * @var string
     */
    public $description;
    /**
     * Доступность таксономии
     * @var boolean
     */
    public $public = true;
    /**
     * Иерархическая ли таксономия
     * @var boolean
     */
    public $hierarchical = false;
    /**
     * Доступность в админке
     * @var boolean
     */
    public $show_ui = true;
    /**
     * Доступность в меню админке
     * @var boolean
     */
    public $show_in_menu = true;
    /**
     * Доступность в меню навигации
     * @var boolean
     */
    public $show_in_nav_menus = true;
    /**
     * Доступность в облаке тегов
     * @var boolean
     */
    public $show_tagcloud = true;
    /**
     * Доступность быстрого редактирования
     * @var boolean
     */
    public $show_in_quick_edit = true;
    /**
     * Показывать ли таксономию в листинге объектов
     * @var boolean
     */
    public $show_admin_column = false;
    /**
     * Метабокс для таксономии
     * @var boolean|callable
     */
    public $meta_box_cb;
    /**
     * Права доступа
     * @var array
     */
    public $capabilities = [];
    /**
     * Rewrite
     * @var boolean|array
     */
    public $rewrite;
    /**
     * Query var
     * @var string
     */
    public $query_var;
    /**
     * Функция обновления количества записей
     * @var callable
     */
    public $update_count_callback;
    
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        add_action('init',[$this,'register']);
    }
    
    public function register() {
        register_taxonomy($this->taxonomy, $this->object_type, $this->getAttributes());
    }
    
    public function getAttributes() {
        $atts = get_object_vars($this);
        if (is_null($atts['query_var'])) {
            $atts['query_var'] = $this->taxonomy;
        }
        unset($atts['taxonomy']);
        unset($atts['object_type']);
        return $atts;
    }
    
    /**
     * Инициализация лейблов
     * @param string $sing1 именительный (кто? что?)
     * @param string $sing2 винительный (кого? что?)
     * @param string $many1 именительный (кто? что?)
     * @param string $many2 винительный (кого? что?)
     */
    public function initLabels($sing1, $sing2, $many1, $many2) {
        $this->labels = [];
        $this->labels['name']                   = $many1;
        $this->labels['singular_name']          = $sing1;
        $this->labels['menu_name']              = $many1;
        $this->labels['all_items']              = "Все ".$many1;
        $this->labels['add_new_item']           = "Добавить ".$sing2;
        $this->labels['edit_item']              = "Редактировать ".$sing2;
        $this->labels['update_item']            = "Обновить ".$sing2;
        $this->labels['view_item']              = "Посмотреть ".$sing2;
        $this->labels['new_item_name']          = "New ".$sing1;
        $this->labels['parent_item']            = "Родительский элемент";
        $this->labels['parent_item_colon']      = "Родительский элемент:";
        $this->labels['search_items']           = "Поиск ".$many2;
        $this->labels['popular_items']          = "Популярные ".$many1;
        $this->labels['separate_items_with_commas'] = null;
        $this->labels['add_or_remove_items']        = "Добавить или удалить ".$many2;
        $this->labels['choose_from_most_used']      = "Часто используемые ".$many1;
        $this->labels['not_found']                  = $many2." не найдены";
    }
}