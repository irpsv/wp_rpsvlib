<?php

namespace FCL;

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