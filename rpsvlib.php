<?php

/**
 * Один единственный файл библиотеки для удобства подключения,
 * да и не так уж и много здесь кода.
 * 
 * @version 0.9.1
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
    const SUPPORTS_TITLE = 'editor';
    /**
     * блок выбора автора
     */
    const SUPPORTS_TITLE = 'author';
    /**
     * блок миниатюры
     */
    const SUPPORTS_TITLE = 'thumbnail';
    /**
     * блок ввода цитаты
     */
    const SUPPORTS_TITLE = 'excerpt';
    /**
     * блок уведомлений
     */
    const SUPPORTS_TITLE = 'trackbacks';
    /**
     *  блок установки произвольных полей
     */
    const SUPPORTS_TITLE = 'custom-fields';
    /**
     * блок комментариев
     */
    const SUPPORTS_TITLE = 'comments';
    /**
     * блок ревизий (не отображается пока нет ревизий)
     */
    const SUPPORTS_TITLE = 'revisions';
    /**
     * блок атрибутов постоянных страниц
     * (шаблон и древовидная связь записей, древовидность должна быть включена)
     */
    const SUPPORTS_TITLE = 'page-attributes';
    /**
     * блок форматов записи, если они включены в теме
     */
    const SUPPORTS_TITLE = 'post-formats';
    
    /**
     * Атрибуты типа записи
     * @var array
     */
    private $attributes = [
        "public" => true
    ];
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
        if (in_array($name, ['type','labels'])) {
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
    public function __construct($postType, $labels = [], $attributes = []) {
        $this->type = $postType;
        $this->labels = $labels;
        $this->attributes = $attributes;
        add_action('init',[$this, 'register']);
    }
    
    public function register() {
        $this->attributes['labels'] = $this->labels;
        register_post_type($this->type, $this->attributes);
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
    private $attributes = [];
    
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
        $this->attributes[] = $item;
    }
    
    /**
     * Вызывает функцию, которая создает список элементов бокса.
     * Данный функционал необходим, например для связки элементов бокса между собой.
     * @param callable $callback функция в параметре которой отправялетя ССЫЛКА на список атриубтов
     */
    public function init($callback) {
        call_user_func_array($callback, [& $this->attributes]);
    }

    /**
     * Отрисовка всех элементов бокса
     */
    public function render() {
        wp_nonce_field($this->id, sha1($this->id));
        foreach ($this->attributes as $attr) {
            echo $attr->render();
        }
    }
    
    /**
     * Сохранение поста
     * @param int $idPost ID сохраняемого поста
     * @return boolean FALSE - если не прошла валидация, TRUE - иначе
     */
    public function save($idPost) {
        if ($this->validate()) {
            foreach ($this->attributes as $attr) {
                $attr->save($idPost);
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