<?php

namespace FCL;

class Post
{
    /**
     * Тип поста
     * @var string
     */
    public $post_type;
    /**
     * Заголовок (default: $post_type)
     * @var string
     */
    public $label;
    /**
     * Заголовки
     * @var array
     */
    public $labels;
    /**
     * Краткое описание типа записи
     * @var string
     */
    public $description = '';
    /**
     * Иерархический тип записи. Если TRUE, то появляется возможность выбирать родителя и доступен элемент 'page-attributes'.
     * @var boolean 
     */
    public $hierarchical = false;
    /**
     * Видимость типа записи (влияет на $exclude_from_search, $public_queryable, $show_ui, $show_in_nav_menus)
     * @var boolean
     */
    public $public = true;
    /**
     * Исключить ли данный тип из front-end поиска записей (false - доступен для поиска)
     * @var boolean
     */
    public $exclude_from_search = false;
    /**
     * Могут ли выполнятся запросы 'parse_request()' во front-end'e (true - могут)
     * @var boolean
     */
    public $public_queryable = true;
    /**
     * Доступен ли данный тип записей в админке (true - показывать)
     * @var boolean
     */
    public $show_ui = true;
    /**
     * Показывать ли данный тип записей в меню админки (true - показывать)
     * @var boolean
     */
    public $show_in_menu = true;
    /**
     * Показывать ли данный тип записей в меню навигации (true - показывать)
     * @var boolean
     */
    public $show_in_nav_menus = true;
    /**
     * Показывать ли данный тип записей в admin_bar (true - показывать)
     * @var boolean
     */
    public $show_in_admin_bar = true;
    /**
     * Позиция элемента в меню админки (по умолчанию после комментариев)
     * @var integer
     */
    public $menu_position;
    /**
     * URL до иконки или название dashicon иконки
     * @var string
     */
    public $menu_icon;
    /**
     * Параметр для построение массива прав ($capabilities).
     * Пример: 'book' или array('book','books')
     * @var string|array
     */
    public $capability_type = 'post';
    /**
     * Список прав
     * @var array
     */
    public $capabilities = [];
    /**
     * Whether to use the internal default meta capability handling.
     * @var boolean
     */
    public $map_meta_cap;
    /**
     * Список элементов формы редактирования
     * @var array
     */
    public $supports = [Support::TITLE, Support::EDITOR];
    /**
     * Функция обработки метабоксов после события 'save_post'
     * @var callable
     */
    public $register_meta_box_cb;
    /**
     * Таксономии для типа записи
     * @var array
     */
    public $taxonomies = [];
    /**
     * Доступен ли архив записей
     * @var boolean
     */
    public $has_archive = false;
    /**
     * @var boolean|array
     */
    public $rewrite = true;
    /**
     * Параметры запроса (Подробнее: https://codex.wordpress.org/WordPress_Query_Vars)
     * @var boolean|string
     */
    public $query_var = true;
    /**
     * Возможность экспорта
     * @var boolean
     */
    public $can_export = true;
    /**
     * Удалять ли все посты данного типа если удаляется их автор
     * @var boolean
     */
    public $delete_with_user;
    
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        add_action('init',[$this,'register']);
    }
    
    public function register() {
        register_post_type($this->post_type, $this->getAttributes());
    }

    public function getAttributes() {
        $ret = get_object_vars($this);
        unset($ret['post_type']);
        return array_filter($ret, function($value) {
            return !is_null($value);
        });
    }
    
    /**
     * Инициализация лейблов
     * @param string $sing1 именительный (кто? что?)
     * @param string $sing2 родительный (кого? чего?)
     * @param string $many1 именительный (кто? что?)
     * @param string $many2 родительный (кого? чего?)
     */
    public function initLabels($sing1, $sing2, $many1, $many2, $menuName = null) {
        $this->labels = [];
        $this->labels['name']                   = $many1;
        $this->labels['singular_name']          = $sing1;
        $this->labels['add_new']                = "Добавить";
        $this->labels['add_new_item']           = "Добавить ".$sing2;
        $this->labels['edit_item']              = "Редактировать ".$sing2;
        $this->labels['new_item']               = "Создать ".$sing2;
        $this->labels['view_item']              = "Посмотр ".$sing2;
        $this->labels['search_items']           = "Поиск ".$many2;
        $this->labels['not_found']              = $many1." не найдены";
        $this->labels['not_found_in_trash']     = $many1." в корзине не найдены";
        $this->labels['parent_item_colon']      = "Родительская {$sing1}:";
        $this->labels['all_items']              = "Все ".$many1;
        $this->labels['archives']               = "Архив ".$many2;
        $this->labels['insert_into_item']       = "Вставить в ".$sing2;
        $this->labels['uploaded_to_this_item']  = "Uploaded to this ".$sing1;
        $this->labels['featured_image']         = "Миниатюра";
        $this->labels['set_featured_image']     = "Выбрать миниатюру";
        $this->labels['remove_featured_image']  = "Удалить";
        $this->labels['use_featured_image']     = "Использовать минатюру";
        $this->labels['menu_name']              = $menuName ? $menuName : $many1;
        $this->labels['filter_items_list']      = "Filter items list";
        $this->labels['items_list_navigation']  = "Items list navigation";
        $this->labels['items_list']             = "Items list";
    }
}