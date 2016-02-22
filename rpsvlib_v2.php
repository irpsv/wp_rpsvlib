<?php

namespace RPSV;

abstract class Object
{
    public function __construct($attributes = []) {
        foreach ($attributes as $property => $value) {
            $this->$property = $value;
        }
    }
    
    public function __set($name, $value) {
        $methodName = 'set'.$name;
        if (method_exists($this, $methodName)) {
            $this->$methodName($value);
        }
        elseif (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
}

class Capability
{
    const ROLE_SUPER_ADMIN = 'manage_network';
    const ROLE_ADMIN = 'activate_plugins';
    const ROLE_EDITOR = 'moderate_comments';
    const ROLE_AUTHOR = 'edit_published_posts';
    const ROLE_CONTRIBUTOR = 'edit_posts';
    const ROLE_SUBSCRIBER = 'read';
}

class Support
{
    /**
     * Заголовок
     */
    const TITLE             = 'title';
    /**
     * Редактор содержания
     */
    const EDITOR            = 'editor';
    /**
     * Автор
     */
    const AUTHOR            = 'author';
    /**
     * Миниатюра
     */
    const THUMBNAIL         = 'thumbnail';
    /**
     * Цитата - краткое содержание доступное из функции 'the_excerpt()'
     */
    const EXCERPT           = 'excerpt';
    /**
     * Обратные ссылки
     */
    const TRACKBACKS        = 'trackbacks';
    /**
     * Пользоватльеские поля
     */
    const CUSTOM_FIELDS     = 'custom-fields';
    /**
     * Блок управления комментариями и обратными ссылками 
     * (появляется автоматически если отображается 'trackbacks')
     */
    const COMMENTS          = 'comments';
    /**
     * ???
     */
    const REVISIONS         = 'revisions';
    /**
     * Атрибуты страницы
     */
    const PAGE_ATTRIBUTES   = 'page-attributes';
    /**
     * ???
     */
    const POST_FORMATS      = 'post-formats';
}

class StringHelper
{
    public static function isNullStr($str) {
        return is_null($str) || trim($str) == "";
    }

    public static function substrWithPostfix($str, $length, $postfix) {
        return substr($str, 0, $length).(strlen($str) > $length ? $postfix : "");
    }

    public static function generate($str, $count) {
        $ret = "";
        while ($count--) {
            $ret .= $str;
        }
        return $ret;
    }
}

class ArrayHelper
{
    public static function getIsSet(array $arr, $key, $defaultValue = null) {
        return isset($arr[$key]) ? $arr[$key] : $defaultValue;
    }
    
    public static function getOf_GET($key, $defaultValue = null) {
        return self::getIsSet($_GET, $key, $defaultValue);
    }
    
    public static function getOf_POST($key, $defaultValue = null) {
        return self::getIsSet($_POST, $key, $defaultValue);
    }
    
    public static function getOf_REQUEST($key, $defaultValue = null) {
        return self::getIsSet($_REQUEST, $key, $defaultValue);
    }
    
    public static function restrictFilesItem($files) {
        $ret = array();
        for ($i=0; isset($files['name'][$i]); $i++) {
            $ret[] = array(
                "name" => $files['name'][$i],
                "type" => $files['type'][$i],
                "tmp_name" => $files['tmp_name'][$i],
                "size" => $files['size'][$i],
                "error" => $files['error'][$i],
            );
        }
        return $ret;
    }
}

class HtmlHelper
{
    public static function select($options, $values, $selectedValue, $isUseKey = true) {
        $html = "<select {$options}>";
        foreach ($values as $key => $val) {
            if ($isUseKey) {
                $selected = $selectedValue == $key ? " selected='selected' " : "";
                $html .= "<option value='{$key}' {$selected}>{$val}</option>";
            }
            else {
                $selected = $selectedValue == $val ? " selected='selected' " : "";
                $html .= "<option {$selected}>{$val}</option>";
            }
        }
        return $html . "</select>";
    }
}

/*
 * **********************************************
 * **************** SHORTCODE *******************
 * **********************************************
 */

abstract class Shortcode extends Object
{
    /**
     * Название шорткода
     * @var string
     */
    public $name;
    /**
     * Значения параметров по умолчанию
     * @var array
     */
    public $default = [];
    public $render;
    
    public function __construct($attributes = array(), $isAutoRegister = false) {
        parent::__construct($attributes);
        if ($isAutoRegister) {
            $this->register();
        }
    }
    
    public function register() {
        add_shortcode($this->name, [$this, 'render']);
    }
    
    abstract public function render($atts);
}

/**
 * Шорткод, отрисовка которого производиться из файла
 * @property string $render путь до файла отвечающего за отрисовку
 */
class ShortcodeFile extends Shortcode
{
    public function render($atts) {
        extract(shortcode_atts($this->default, $atts));
        ob_start();
        include $this->render;
        return ob_get_clean();
    }
}

/**
 * Шорткод, отрисовка которого производиться из функции
 * @property callable $render функция отрисовки
 */
class ShortcodeFunc extends Shortcode
{
    public function render($atts) {
        return call_user_func(
            $this->render,
            shortcode_atts($this->default, $atts)
        );
    }
}


/*
 * **********************************************
 * **************** ADMIN PAGE ******************
 * **********************************************
 */

abstract class ObjectPage extends Object
{
    /**
     * Заголовок страницы
     * @var string
     */
    public $page_title;
    /**
     * Заголовок в меню
     * @var string
     */
    public $menu_title;
    /**
     * Права пользователя
     * @var string
     */
    public $capability = Capability::ROLE_ADMIN;
    /**
     * Название
     * @var string
     */
    public $menu_slug;
    /**
     * Функция отвечающая за отрисовку
     * @var callable
     */
    public $function;
    
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        $this->init();
    }
    
    public function setTitle($title) {
        $this->page_title = $title;
        $this->menu_title = $title;
    }
    
    public function renderFile($filepath, $attributes = null) {
        $this->function = function($params) use ($filepath,$attributes) {
            include $filepath;
        };
    }
    
    abstract public function init();
}

class AdminPage extends ObjectPage
{
    public $position;
    public $icon_url;
    
    public function init() {
        add_action('admin_menu',function(){
            $this->register();
        });
    }
    
    public function register() {
        add_menu_page(
            $this->page_title,
            $this->menu_title,
            $this->capability,
            $this->menu_slug,
            $this->function,
            $this->icon_url,
            $this->position
        );
    }
}

class AdminSubPage extends ObjectPage
{
    public $hook = 'admin_menu';
    public $parent_slug;
    
    public function init() {
        add_action($this->hook,[$this,'register']);
    }
    
    public function register() {
        add_submenu_page(
            $this->parent_slug,
            $this->page_title,
            $this->menu_title,
            $this->capability,
            $this->menu_slug,
            $this->function
        );
    }
}

/*
 * **********************************************
 * ****************** POST **********************
 * **********************************************
 */

class PostStatus
{
    /**
     * Опубликован
     */
    const PUBLISH       = 'publish';
    /**
     * Будет опубликован в будующем
     */
    const FUTURE        = 'future';
    /**
     * Черновик
     */
    const DRAFT         = 'draft';
    /**
     * На рассмотрении
     */
    const PENDING       = 'pending';
    /**
     * Приватный (только для админов)
     */
    const PRIVATE_      = 'private';
    /**
     * Удален
     */
    const TRASH         = 'trash';
    /**
     * Авто-черновик (авто-сохранение)
     */
    const AUTO_DRAFT    = 'auto-draft';
    /**
     * Дочерний элемент (например: вложение)
     */
    const INHERIT       = 'inherit';
}

class Post extends Object
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
        return $ret;
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
        $this->labels['add_new_item']           = "Добавление ".$sing2;
        $this->labels['edit_item']              = "Редактирование ".$sing2;
        $this->labels['new_item']               = "Создание ".$sing2;
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

/**
 * Посты только для админки (не показываются во front-end'e)
 */
class PostPrivate extends Post
{
    public function __construct($attributes = array()) {
        $this->public = false;
        $this->show_ui = true;
        parent::__construct($attributes);
    }
}

/*
 * **********************************************
 * **************** METABOX *********************
 * **********************************************
 */

class MetaboxContext
{
    const NORMAL    = 'normal';
    const SIDE      = 'side';
    const ADVANCED  = 'advanced';
}

class MetaboxPriority
{
    const HIGH  = 'high';
    const LOW   = 'low';
}

class Metabox extends Object
{
    public $id; // required
    public $title; // required
    public $screen;
    public $context = MetaboxContext::ADVANCED;
    public $priority = 'default';
    public $callback; // required
    public $callback_args;
    //
    public $post_type;
    public $saveCallback; // required
    
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        //
        $hook = 'add_meta_boxes';
        if ($this->post_type) {
            $hook .= "_".$this->post_type;
        }
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

/*
 * **********************************************
 * ****************** TAX ***********************
 * **********************************************
 */

class Tax extends Object
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
    
    public function getTerms($fields = 'names') {
        return get_terms($this->taxonomy, [
            "orderby" => "name",
            "fields" => $fields,
            "get" => "all"
        ]);
    }
    
    public function getValues($post = null) {
        return get_the_terms($post, $this->taxonomy);
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

/*
 * **********************************************
 * *************** GRID VIEW ********************
 * **********************************************
 */
abstract class GridView extends Object
{
    public $columns;
    
    abstract public function getHookFilterName();
    abstract public function getHookActionName();
    
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        add_action('init',[$this,'register']);
    }
    
    public function addColumn($name, $label, $render) {
        $this->columns[$name] = [
            "label" => $label,
            "render" => $render
        ];
    }
    
    public function register() {
        add_filter($this->getHookFilterName(), [$this,'manageColumns']);
        add_action($this->getHookActionName(), [$this,'renderColumn'], 10, 2);
    }
    
    public function manageColumns($columns) {
        foreach ($this->columns as $name => $data) {
            $columns[$name] = $data['label'];
        }
        return $columns;
    }
    
    public function renderColumn($column, $id_post) {
        $data = ArrayHelper::getIsSet($this->columns, $column, false);
        if ($data) {
            echo call_user_func($data['render'], $id_post);
        }
    }
}

class GridViewPost extends GridView
{
    public $post_type;
    
    public function getHookFilterName() {
        if ($this->post_type) {
            return "manage_{$this->post_type}_posts_columns";
        }
        return 'manage_posts_columns';
    }
    
    public function getHookActionName() {
        if ($this->post_type) {
            return "manage_{$this->post_type}_posts_custom_column";
        }
        return 'manage_posts_custom_column';
    }
}

/**
 * NOT WORKING
 *//*
class GridViewTax extends GridView
{
    public $taxonomy;
    
    public function getHookFilterName() {
        return "manage_{$this->taxonomy}_columns";
    }
    
    public function getHookActionName() {
        return "manage_{$this->taxonomy}_custom_column";
    }
}*/