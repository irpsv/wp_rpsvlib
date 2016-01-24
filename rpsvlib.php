<?php

/*
Plugin Name: RPSVlib
Description: библиотека для облегчения написания кода и работы с API WP (можно не подключать, а просто include'ить)
Version: 0.12.14
Author: I.RPSV
*/

/**
 * Один единственный файл библиотеки для удобства подключения,
 * да и не так уж и много здесь кода.
 * 
 * @version 0.9.14
 */

namespace RPSV;

/**
 * Класс для добавления скриптов по ходу рендинга представления
 */
abstract class JsFooterScript {
    private static $data = "";
    
    public static function add($str) {
        self::$data .= $str;
    }
    
    public static function render() {
        echo '<script type="text/javascript">jQuery(function($){'.self::$data.';})</script>';
    }
}

/**
 * Класс содержащий методы которые не вошли в другие классы по каким либо соображениям
 */
abstract class StringHelper
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

/**
 * Класс для работы с массивами
 */
abstract class ArrayHelper
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
    
    public static function map($arr, $func, $filterFunc = null) {
        $ret = array();
        foreach ($arr as $key => $value) {
            if (!is_null($filterFunc)
                    && call_user_func_array($filterFunc, array($key, $value)) == FALSE)
            {
                continue;
            }
            $ret[$key] = call_user_func($func, $value);
        }
        return $ret;
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
            return ob_end_clean();
        }
        catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
}

class DbHelper
{
    public static function query($q) {
        /* @var $wpdb \wpdb */
        global $wpdb;
        return $wpdb->query($q);
    }
    
    public static function startTransaction() {
        return self::query('START TRANSACTION');
    }
    
    public static function commit() {
        return self::query('COMMIT');
    }
    
    public static function rollback() {
        return self::query('ROLLBACK');
    }
    
    public static function transaction($callback) {
        try {
            self::startTransaction();
            $callback();
            self::commit();
        } catch (Exception $ex) {
            self::rollback();
            throw $ex;
        }
    }
}

/**
 * Класс для создания таксономий
 * 
 * @property boolean $public Показывать ли эту таксономию в интерфейсе админ-панели (default: true)
 * @property boolean $show_in_nav_menus true даст возможность выбирать элементы этой таксономии в навигационном меню (default: public)
 * @property boolean $show_ui Показывать блок управления этой таксономией в админке (default: true)
 * @property boolean $show_tagcloud Создать виджет облако элеметнов этой таксономии (default: true)
 * @property boolean $hierarchical true - таксономия будет древовидная (как категории). false - будет не древовидная (как метки) (default:false)
 * @property string $update_count_callback Название функции, которая будет вызываться для обновления количества записей в данной таксономии, для типа(ов) записей которые ассоциированы с этой таксономией
 * @property mixed $rewrite false - выключит перезапись. Если указать массив, то можно задать произвольный параметр запроса (query var). По умолчанию будет использоваться параметр $taxonomy. <br> Аргументы массива: <ul><li>slug - предваряет посты этой строкой. По умолчанию название таксономии;</li><li>with_front - позволяет установить префикс для постоянной ссылки, по умолчанию true;</li><li>hierarchical - true или false включает древовидные УРЛы (с версии 3.1)</li></ul>
 * @property mixed $query_var Если указать false, выключит параметры запроса и сам запрос. Или можно указать строку, чтобы изменить параметр запроса (query var). По умолчанию будет использоваться параметр $taxonomy - название таксономии.
 * @property array $capabilities Массив прав для этой таксономии
 * @property callable $meta_box_cb callback функция. Отвечает за то, как будет отображаться таксономия в метабоксе (с версии 3.8). Встроенные названия функций: <ul><li>post_categories_meta_box - показывать как категории,</li><li>post_tags_meta_box - показывать как метки.</li></ul> Если указать false, то метабокс будет отключен вообще.
 * @property boolean $show_admin_column Позволить или нет авто-создание колонки таксономии в таблице ассоциированного типа записи. (с версии 3.5)
 * @property boolean $sort Следует ли этой таксономии запоминать порядок в котором созданные элементы (термины) прикрепляются к объектам (записям).
 * @property boolean $show_in_quick_edit Показывать ли таксономию в панели быстрого редактирования записи (в таблице, списке всех записей, при нажатии на кнопку "свойства"). С версии 4.2.
 * @property bollean $_builtin Параметр предназначен для разработчиков. Если переключить на true, то это будет означать что эта таксономия относится к внутренним таксономия WordPress и не является встроенной (кастомной).
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
 * 
 * @property string $description Короткое описание этого типа записи
 * @property boolean $public Аргумент определяющий показ пользовательского интерфейса этой менюшки, т.е. показывать ли эту менюшку в админ-панели. <ul><li>false - не показывать пользовательский интерфейс (UI) для этого типа записей (show_ui=false), запросы относящиеся к этому типу записей не будут работать в шаблоне (publicly_queryable=false), этот тип записей не будет учитываться при поиске по сайту (exclude_from_search=true), этот тип записей будет спрятан из выбора меню навигации (show_in_nav_menus=false).</li><li>true - show_ui=true, publicly_queryable=true, exclude_from_search=false, show_in_nav_menus=true</li></ul>
 * @property boolean $publicly_queryable Запросы относящиеся к этому типу записей будут работать во фронтэнде (в шаблоне сайта)
 * @property boolean $exclude_from_search Исключить ли этот тип записей из поиска по сайту. true - да, false - нет
 * @property boolean $show_ui Показывать ли меню для управления этим типом записи в админ-панели. false- не показывать меню, true - показывать меню в админ-панели
 * @property mixed $show_in_menu Показывать ли тип записи в администраторском меню и где именно показывать управление этим типом записи. Аргумент show_ui должен быть включен!<ul><li>false - не показывать в администраторском меню;</li><li>true - показывать как меню первого уровня;</li><li>строка - показывать как страницу первого уровня, как например 'tools.php' или 'edit.php?post_type=page'</li></ul>
 * @property integer $menu_position Позиция где должно расположится меню нового типа записи:<ul><li>1 — в самом верху меню</li><li>2-3 — под «Консоль»</li><li>4-9 — под «Записи»</li><li>10-14 — под «Медиафайлы»</li><li>15-19 — под «Ссылки»</li><li>20-24 — под «Страницы»</li><li>25-59 — под «Комментарии» (по умолчанию, null)</li><li>60-64 — под «Внешний вид»</li><li>65-69 — под «Плагины»</li><li>70-74 — под «Пользователи»</li><li>75-79 — под «Инструменты»</li><li>80-99 — под «Параметры»</li><li>100+ — под разделителем после «Параметры»</li></ul>
 * @property string $menu_icon Ссылка на картинку, которая будет использоваться для этого меню. С выходом WordPress 3.8 появился новый пакет иконок Dashicons, который входит в состав ядра WordPress. Это комплект из более 150 векторных изображений. Чтобы установит одну из иконок, напишите её название в этот параметр. Например иконка постов, называется так: dashicons-admin-post, а ссылок dashicons-admin-links
 * @property mixed $capability_type Строка которая будет маркером для установки группы прав относительного этого типа записи. Можно передавать массив, где первое значение будет использоваться для единственного числа, а второе для множественного, например: array('story', 'stories'). Если передается строка, то для множественного числа просто прибавляется 's' на конце. capability_type используется для построения списка прав, которые будут записаны в параметр 'capabilities'. При установке нестандартного маркера (не post или page), параметр map_meta_cap нужно установить в true, а в параметре 'capabilities' можно добавить дополнительные права, которые не были установлены системой
 * @property array $capabilities Массив прав для этого типа записи. По умолчанию: используется аргумент capability_type для построения списка разрешений
 * @property boolean $map_meta_cap Ставим true чтобы включить дефолтный обработчик специальных прав map_meta_cap(). Он преобразует неоднозначные права (edit_post - один пользователь может, а другой нет) в примитивные (edit_posts - все пользователи могут). Обычно для типов постов этот нужно включать, если типу поста устанавливаются особые права (отличные от 'post').
 * @property boolean $hierarchical Будут ли записи этого типа иметь древовидную структуру (как постоянные страницы). true - да, будут древовидными, false - нет, будут связаны тексономией (категориями)
 * @property array $supports Вспомогательные поля на странице создания/редактирования этого типа записи. Метки для вызова функции add_post_type_support()
 * @property callable $register_meta_box_cb callback функция, которая будет срабатывать при установки мета блоков для страницы создания/редактирования этого типа записи. Используйте remove_meta_box() и add_meta_box() в callback функции
 * @property array $taxonomies Массив зарегистрированных таксономий, которые будут связанны с этим типом записей, например: category или post_tag. Может быть использовано вместо вызова функции register_taxonomy_for_object_type(). Таксономии нужно регистрировать с помощью функции register_taxonomy()
 * @property string $permalink_epmask
 * @property boolean $has_archive Включить поддержку страниц архивов для этого типа записей (пр. УРЛ записи выглядит так: site.ru/type/post_name, тогда УРЛ архива будет такой: site.ru/type. файл этого архива в теме будет иметь вид archive-type.php). Для архивов будет добавлено новое правило перезаписи УРЛов, если аргумент rewrite включен
 * @property mixed $rewrite Использовать ли ЧПУ для этого типа записи. Чтобы не использовать ЧПУ укажите false. По умолчанию true - название типа записи используется как префикс в ссылке. Можно указать дополнительные параметры для построения ЧПУ в массиве: <ul><li>slug (строка): Префикс в ЧПУ (/префикс/ярлык_записи). Используйте array( 'slug' => $slug ), чтобы создать другой префикс. В этом параметре можно указывать плейсхолдеры типа %category%. Но их нужно создать с помощью add_rewrite_tag() и научить WP их понимать. По умолчанию: название типа записи</li><li>with_front (логический): Нужно ли в начало вставлять общий префикс из настроек. Префикс берется из $wp_rewite->front. Например, если структура постоянных ссылок записей в настройках имеет вид blog/%postname%, то при false получим: /news/название_поста, а при true получим: /blog/news/название_поста. По умолчанию: true</li><li>feeds (логический): Добавить ли правило ЧПУ для RSS ленты этого типа записи. По умолчанию: значение аргумента has_archive</li><li>pages (логический): Добавить ли правило ЧПУ для пагинации архива записей этого типа. Пр: /post_type/paged/2. По умолчанию: true</li></ul>
 * @property mixed $query_var Ставим false, чтобы убрать возможность запросов или устанавливаем название запроса для этого типа записей
 * @property boolean $can_export Возможность экспорта этого типа записей
 * @property boolean $show_in_nav_menus Включить возможность выбирать этот тип записи в меню навигации
 * @property boolean $delete_with_user <ul><li>true - удалять записи этого типа принадлежащие пользователю при удалении пользователя. Если включена корзина, записи не удаляться, а поместятся в корзину.</li><li>false - при удалении пользователя его записи этого типа никак не будут обрабатываться.</li><li>null - записи удаляться или будут перемещены в корзину, если post_type_supports('author') установлена. И не обработаются, если поддержки 'author' у типа записи нет.</li></ul> По умолчанию: null
 * @property boolean $_builtin Для внутреннего использования! True, если это встроенный/внутренний типа записи
 * @property string $_edit_link Для внутреннего использования! Часть URL в ссылке на редактирования этого типа записи
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
    public function __construct($postType, $labels = [], $attributes = ["public" => true], $priority = 10) {
        $this->type = $postType;
        $this->labels = $labels;
        $this->attributes = $attributes;
        add_action('init',[$this, 'register'],$priority);
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
class Metabox
{
    /**
     * ID контейнера метабокса
     */
    public $id;
    /**
     * Заголовок/название блока. Виден пользователям.
     */
    public $title;
    public $postType = 'post';
    public $context ='advanced';
    public $priority = 'default';
    public $callbackArgs = null;
    /**
     * Функция которая отвечает за отрисовку
     * @var callable
     */
    public $renderCallback;
    /**
     * Функция которая отвечает за сохранение элемента
     * @var callable
     */
    public $saveCallback;
    
    public function __construct($id, $title) {
        $this->id = $id;
        $this->title = $title;
        $this->init();
        add_action('save_post',[$this, 'save']);
        add_action('add_meta_boxes',[$this, 'addMetaBox']);
    }
    
    public function init() {
        $this->renderCallback = function($model) {
            global $post;
            $value = get_post_meta($post->ID, $model->name, true);
            echo "<input type='text' name='{$model->id}' value='{$value}' />";
        };
        $this->saveCallback = function($model, $id_post) {
            $name = $model->name;
            $value = ArrayHelper::getOf_POST($name);
            if (is_null($value) || trim($value) == "") {
                delete_post_meta($id_post, $name);
            }
            else {
                update_post_meta($id_post, $name, $value);
            }
        };
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
    
    /**
     * Отрисовка всех элементов бокса
     */
    public function render() {
        wp_nonce_field($this->id, sha1($this->id));
        if (is_callable($this->renderCallback)) {
            $f = $this->renderCallback;
            $f($this);
        }
        else {
            $model = & $this;
            include $this->renderCallback;
        }
    }
    
    /**
     * Сохранение поста
     * @param int $id_post ID сохраняемого поста
     * @return boolean FALSE - если не прошла валидация, TRUE - иначе
     */
    public function save($id_post) {
        if ($this->validate()) {
            $f = $this->saveCallback;
            return $f($this, $id_post);
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
                ArrayHelper::getOf_POST(sha1($this->id)),
                $this->id
            );
        }
        return false;
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