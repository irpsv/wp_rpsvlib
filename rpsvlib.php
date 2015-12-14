<?php

/**
 * Один единственный файл библиотеки для удобства подключения,
 * да и не так уж и много здесь кода.
 * 
 * @version 0.9.11
 */

namespace RPSV;

/**
 * Класс для работы с массивами и наборами значений
 */
abstract class ArrayHelper {
    /**
     * Возвращает значение ключа массива если оно существует, иначе возвращает значение по умолчанию
     * @param string $key интересующий ключ массива
     * @param array $arr массив с даными
     * @param mixed $defaultValue значение которое вернется в случае отсутствия искомого элемента
     * @return midex искомый элемент или же $defaultValue
     */
    public static function getIsSet($key, array $arr, $defaultValue = null) {
        if (isset($arr[$key])) {
            return $arr[$key];
        }
        return $defaultValue;
    }
    
    /**
     * Возвращает ключ искомого значени
     * @param mixed $needValue значение для которого нужно вернуть ключ
     * @param array $arr массив с даными
     * @param mixed $defaultKey значение которое вернется в случае отсутствия искомого элемента
     * @return mixed ключ искомого элемента или же $defaultValue
     */
    public static function getKeyOfValue($needValue, array $arr, $defaultKey = null) {
        foreach ($arr as $key => $value) {
            if ($needValue == $value) {
                return $key;
            }
        }
        return $defaultKey;
    }

    /**
     * Удаляет из массива пустые элементы и пустые строки
     * @param array $arr
     */
    public static function removeToEmptyItem(array $arr, $isRemoveNull = true, $isRemoveNullStr = true) {
        $ret = [];
        foreach ($arr as $item) {
            if (trim($item) === "") {
                if (!$isRemoveNullStr) {
                    $ret[] = $item;
                }
            }
            elseif (is_null($item)) {
                if (!$isRemoveNull) {
                    $ret[] = $item;
                }
            }
            else {
                $ret[] = $item;
            }
        }
        return $ret;
    }
    
    /**
     * Удаляет из ассоциативного массива пустые элементы и пустые строки
     * @param array $arr
     */
    public static function removeToEmptyItemAssoc(array $arr, $isRemoveNull = true, $isRemoveNullStr = true) {
        $ret = [];
        foreach ($arr as $key => $val) {
            if (trim($val) === "") {
                if (!$isRemoveNullStr) {
                    $ret[$key] = $val;
                }
            }
            elseif (is_null($val)) {
                if (!$isRemoveNull) {
                    $ret[$key] = $val;
                }
            }
            else {
                $ret[$key] = $val;
            }
        }
        return $ret;
    }
}

/**
 * Класс для работы с HTML
 */
abstract class HtmlHelper
{
    /**
     * Возвращает HTML код LABEL
     * @param string $for ID элемента для которого выводится LABEL
     * @param string $value значение которое будет в LABEL
     * @param string $options строка с атрибутами тега
     * @return string сформированный HTML
     */
    public static function label($for, $value, $options = '') {
        return "<label for='{$for}' {$options}>{$value}</label>";
    }

    /**
     * Возвращает HTML код INPUT
     * @param string $type тип элемента
     * @param string $id ID элемента
     * @param string $name имя элемента
     * @param string $value значение элемента
     * @param string $options строка с атрибутами тега
     * @return string сформированный HTML
     */
    public static function input($type, $id, $name, $value, $options = '')
    {
        return "<input type='{$type}' id='{$id}' name='{$name}' value='{$value}' {$options} />";
    }
    
    
    /**
     * Возвращает HTML код SELECT
     * @param string $id ID элемента
     * @param string $name имя элемента
     * @param string $selectedValue выбранное значение
     * @param array $values список всех значений списка
     * @param string $options строка с атрибутами тега
     * @return string сформированный HTML
     */
    public static function select($id, $name, $selectedValue, array $values, $options = '') {
        $out = "<select id='{$id}' name='{$name}' {$options}>";
        foreach ($values as $value) {
            $selected = $value == $selectedValue ? " selected " : '';
            $out .= "<option {$selected}>{$value}</option>";
        }
        return $out.'</select>';
    }
    
    /**
     * Отрисовывает и возвращает содержимое файла (буфер отчищается)
     * @param string $path путь до файла, который необходимо подключить
     * @param array $attributes список атрибутов, которые будут распакованы в переменные
     * @return string
     */
    public static function renderFile($path, $attributes) {
        ob_start();
        try {
            if (is_array($attributes) && !empty($attributes)) {
                extract($attributes, EXTR_OVERWRITE);
            }
            include $path;
            return ob_get_contents();
        }
        finally {
            ob_end_clean();
        }
    }
}

/**
 * Класс для работы с запросом
 */
abstract class RequestHelper {
    /**
     * Возвращает значение переменной из глобальной переменной $_POST
     * @param string $name имя искомого поля
     * @param mixed $defaultValue значение которое вернется, если данного поля не существует
     * @return mixed
     */
    public static function getRequestPostValue($name, $defaultValue = null) {
        return ArrayHelper::getIsSet($name, $_POST, $defaultValue);
    }
    
    /**
     * Возвращает значение переменной из глобальной переменной $_GET
     * @param string $name имя искомого поля
     * @param mixed $defaultValue значение которое вернется, если данного поля не существует
     * @return mixed
     */
    public static function getRequestGetValue($name, $defaultValue = null) {
        return ArrayHelper::getIsSet($name, $_GET, $defaultValue);
    }
    
    /**
     * Возвращает значение переменной из глобальной переменной $_REQUEST
     * @param string $name имя искомого поля
     * @param mixed $defaultValue значение которое вернется, если данного поля не существует
     * @return mixed
     */
    public static function getRequestValue($name, $defaultValue = null) {
        return ArrayHelper::getIsSet($name, $_REQUEST, $defaultValue);
    }
}

/**
 * Класс для создания таксономий
 */
class Tax
{
    /**
     * Атрибуты таксономии
     * @var array
     */
    private $attributes = [];
    /**
     * Тип поста, к которой цепляется таксономия
     * @var mixed
     */
    public $postType;
    /**
     * Имя таксономии
     * @var string
     */
    public $name;
    /**
     * Заголовки таксономии
     * @var array
     */
    public $labels;
    
    public function __set($name, $value) {
        if (in_array($name,['labels','name','postType'])) {
            $this->$name = $value;
        }
        else {
            $this->attributes[$name] = $value;
        }
    }
    
    /**
     * Создание товой таксономии
     * @param string $name имя таксономии
     * @param mixed $postType тип поста к которой таксономия цепляется
     * @param array $labels заголовки таксономии
     * @param array $attributes атрибуты таксономии
     */
    public function __construct($name, $postType, $labels = [], $attributes = []) {
        $this->name = $name;
        $this->postType = $postType;
        $this->labels = $labels;
        $this->attributes = $attributes;
        add_action('init',[$this,'register']);
    }
    
    public function register() {
        $this->attributes['labels'] = $this->labels;
        register_taxonomy($this->name, $this->postType, $this->attributes);
    }
    
    /**
     * Инициализирует заголовки
     * @param string $sing1 ед.число именительный падеж (например: "метка", "категория")
     * @param string $sing2 ед.число винительный падеж (например: "метку", "категорию")
     * @param string $many1 мн.число именительный падеж (например: "метки", "категории")
     * @param string $many2 мн.число родительный падеж (например: "меток", "категорий")
     * @param string $menuName пункт меню
     */
    public function initLabels($sing1, $sing2, $many1, $many2, $menuName = null) {
        $this->labels = [
            'name'              => mb_convert_case($many1,MB_CASE_TITLE),
            'singular_name'     => mb_convert_case($sing1,MB_CASE_TITLE),
            'search_items'      => "Поиск {$many2}",
            'popular_items'     => "Популярные {$many1}",
            'all_items'         => "Все {$many1}",
            'parent_item'       => mb_convert_case($sing1,MB_CASE_TITLE)." родитель",
            'parent_item_colon' => mb_convert_case($sing1,MB_CASE_TITLE)." родитель: ",
            'edit_item'         => "Редактировать {$sing2}",
            'update_item'       => "Обновить {$sing2}",
            'add_new_item'      => "Добавить {$sing2}",
            "view_item"         => "Посмотреть {$sing2}",
            'new_item_name'     => "Создать {$sing2}",
            "menu_name"         => is_null($menuName) ? mb_convert_case($many1,MB_CASE_TITLE) : mb_convert_case($menuName,MB_CASE_TITLE)
        ];
    }
}

/**
 * Класс для создания нового типа
 */
class PostType
{
    /*
     * ОПИСАНИЕ для констант взято с сайта http://wp-kama.ru
     */
    
    /**
     * блок заголовка
     */
    const SUPPORTS_TITLE = 'title';
    /**
     * блок ввода контента
     */
    const SUPPORTS_EDITOR = 'editor';
    /**
     * блок выбора автора
     */
    const SUPPORTS_AUTHOR = 'author';
    /**
     * блок миниатюры
     */
    const SUPPORTS_THUMB = 'thumbnail';
    /**
     * блок ввода цитаты
     */
    const SUPPORTS_EXCERPT = 'excerpt';
    /**
     * блок уведомлений
     */
    const SUPPORTS_TRACKBACKS = 'trackbacks';
    /**
     *  блок установки произвольных полей
     */
    const SUPPORTS_CUSTOM_FIELDS = 'custom-fields';
    /**
     * блок комментариев
     */
    const SUPPORTS_COMMENTS = 'comments';
    /**
     * блок ревизий (не отображается пока нет ревизий)
     */
    const SUPPORTS_REVISIONS = 'revisions';
    /**
     * блок атрибутов постоянных страниц
     * (шаблон и древовидная связь записей, древовидность должна быть включена)
     */
    const SUPPORTS_PAGE_ATTRIBUTES = 'page-attributes';
    /**
     * блок форматов записи, если они включены в теме
     */
    const SUPPORTS_POST_FORMATS = 'post-formats';
    
    /**
     * Атрибуты типа записи
     * @var array
     */
    private $attributes;
    /**
     * Название типа записи
     * @var string
     */
    public $type;
    /**
     * Заголовки типа записи
     * @var array
     */
    public $labels;
    
    public function __set($name, $value) {
        if (in_array($name, ['type','labels','attributes'])) {
            $this->$name = $value;
        }
        else {
            $this->attributes[$name] = $value;
        }
    }

    /**
     * Создание нового поста
     * @param string $postType название типа
     * @param array $labels заголовки типа
     * @param array $attributes атрибуты типа
     */
    public function __construct($postType, $labels = [], $attributes = ["public" => true]) {
        $this->type = $postType;
        $this->labels = $labels;
        $this->attributes = $attributes;
        add_action('init',[$this, 'register']);
    }
    
    public function register() {
        $this->attributes['labels'] = $this->labels;
        register_post_type($this->type, $this->attributes);
    }
    
    /**
     * Инициализирует заголовки
     * @param string $sing  единственное число именительный падеж
     * @param string $many множественное число именительный падеж
     * @param string $menuName название меню в админке
     */
    public function initLabels($sing, $many, $menuName = null) {
        $this->labels = [
            "name"                  => mb_convert_case($many,MB_CASE_TITLE),
            "singular_name"         => mb_convert_case($sing,MB_CASE_TITLE),
            "add_new"               => "Добавить {$sing}",
            "add_new_item"          => "Добавить {$sing}",
            "edit_item"             => "Редактировать {$sing}",
            "new_item"              => "Новый {$sing}",
            "view_item"             => "Посмотреть",
            "search_items"          => "Найти {$sing}",
            "not_found"             => mb_convert_case($many,MB_CASE_TITLE)." не найдены",
            "not_found_in_trash"    => mb_convert_case($many,MB_CASE_TITLE)." в корзине не найдены",
            "menu_name"             => is_null($menuName) ? mb_convert_case($many,MB_CASE_TITLE) : mb_convert_case($menuName,MB_CASE_TITLE)
        ];
    }
}

/**
 * Класс для создание формы метабокса
 */
class MetaboxForm
{
    /**
     * Список элементов бокса
     * @var MetaboxFormType[] 
     */
    private $items = [];
    
    public $id;
    public $title;
    public $postType = 'post';
    public $context ='advanced';
    public $priority = 'default';
    public $callbackArgs = null;
    
    public function __construct() {
        add_action('save_post',[$this, 'save']);
        add_action('add_meta_boxes',[$this, 'addMetaBox']);
    }
    
    public function addMetaBox($postType) {
        if ($this->postType == $postType) {
            add_meta_box(
                $this->id,
                $this->title,
                [$this, 'render'],
                $this->postType,
                $this->context,
                $this->priority,
                $this->callbackArgs
            );
        }
    }

    public function addItem(MetaboxFormType $item) {
        $this->items[] = $item;
    }
    
    /**
     * Вызывает функцию, которая создает список элементов бокса.
     * Данный функционал необходим, например для связки элементов бокса между собой.
     * @param callable $callback функция в параметре которой отправялетя ССЫЛКА на список атриубтов
     */
    public function init($callback) {
        call_user_func_array($callback, [& $this->items]);
    }

    /**
     * Отрисовка всех элементов бокса
     */
    public function render() {
        wp_nonce_field($this->id, sha1($this->id));
        foreach ($this->items as $item) {
            echo $item->render();
        }
    }
    
    /**
     * Сохранение поста
     * @param int $idPost ID сохраняемого поста
     * @return boolean FALSE - если не прошла валидация, TRUE - иначе
     */
    public function save($idPost) {
        if ($this->validate()) {
            foreach ($this->items as $item) {
                $item->save($idPost);
            }
            return true;
        }
        return false;
    }

    /**
     * Валидация бокса на соответствие типу и WP Verify NONCE
     * @global WP_Post $post
     * @return boolean TRUE - если бокс валидный, FALSE - иначе
     */
    public function validate() {
        global $post;
        if ($post->post_type == $this->postType) {
            return wp_verify_nonce(
                RequestHelper::getRequestPostValue(sha1($this->id)),
                $this->id
            );
        }
        return false;
    }
}

/**
 * Класс представляющий один элемент формы мета блока
 */
class MetaboxFormType
{
    /**
     * Имя параметра
     * @var string
     */
    public $name;
    /**
     * Заголовок параметра (можно опустить если используется свой RENDER)
     * @var string
     */
    public $label;
    /**
     * Тип параметра (можно опустить если используется свой RENDER)
     * @var string
     */
    public $type = 'text';
    /**
     * Атрибуты LABEL'a (можно опустить если используется свой RENDER)
     * @var string 
     */
    public $optionsLabel = '';
    /**
     * Атрибуты INPUT'а (можно опустить если используется свой RENDER)
     * @var string 
     */
    public $optionsInput = '';
    /**
     * Функция которая отвечает за отрисовку
     * @var callable
     */
    public $render;
    /**
     * Функция которая отвечает за сохранение элемента
     * @var callable
     */
    public $save;
    
    public function __construct($config) {
        $attributes = ['name','type','label','optionsLabel','optionsInput'];
        foreach ($attributes as $attr) {
            if (isset($config[$attr])) {
                $this->$attr = $config[$attr];
            }
        }
        $this->init();
    }
    
    public function init() {
        $this->render = function() {
            global $post;
            //
            echo "<div>";
            echo HtmlHelper::label($this->name,$this->label,$this->optionsLabel);
            echo HtmlHelper::input(
                $this->type,
                $this->name,
                $this->name,
                get_post_meta($post->ID, $this->name, true),
                $this->optionsInput
            );
            echo "</div>";
        };
        //
        $this->save = function($idPost) {
            $name = $this->name;
            $value = RequestHelper::getRequestPostValue($name);
            //
            if (is_null($value)) {
                delete_post_meta($idPost, $name);
            }
            else {
                update_post_meta($idPost, $name, $value);
            }
        };
    }

    /**
     * Отрисовка текущего элемента
     */
    public function render() {
        call_user_func([$this,'render']);
    }
    
    /**
     * Сохранение поста
     * @param int $idPost ID сохраняемого поста
     */
    public function save($idPost) {
        call_user_func([$this,'save'],$idPost);
    }
}

/**
 * Класс для создания меню в админке
 */
class AdminMenu {
    /**
     * Дочерние элементы меню
     * @var array
     */
    private $items = [];
    
    /**
     * Заголовок страницы (тэг <title>)
     * @var string 
     */
    public $pageTitle;
    /**
     * Название пункта меню
     * @var string
     */
    public $menuTitle;
    /**
     * Права пользователя (по умолчанию как у администратора)
     * @var array
     */
    public $capability;
    /**
     * Уникальное название меню
     * @var string
     */
    public $menuSlug;
    /**
     * Функция которая выводит содержание страницы
     * @var mixed
     */
    public $function;
    /**
     * Путь до иконки пункта меню
     * @var string
     */
    public $iconUrl;
    /**
     * Позиция в списке меню
     * @var int
     */
    public $position;
    
    public function __construct() {
        $this->init();
        add_action('admin_menu',[$this,'register']);
    }
    
    public function addItem($pageTitle, $menuTitle, $menuSlug, $capability = null, $function = null) {
        $item = [
            'page_title' => $pageTitle,
            'menu_title' => $menuTitle,
            'capability' => is_null($capability) ? $this->capability : $capability,
            'menu_slug'  => $menuSlug,
        ];
        if (!is_null($function)) {
            $item['function'] = $function;
        }
        $this->items[] = $item;
    }

    public function init() {
        $this->iconUrl = '';
        $this->capability = get_role('administrator')->capabilities;
    }

    public function register() {
        add_menu_page(
            $this->pageTitle,
            $this->menuTitle,
            $this->capability,
            $this->menuSlug,
            $this->function,
            $this->iconUrl,
            $this->position
        );
        foreach ($this->items as $item) {
            add_submenu_page(
                $this->menuSlug,
                $item['page_title'],
                $item['menu_title'],
                $item['capability'],
                $item['menu_slug'],
                $item['function']
            );
        }
    }
}