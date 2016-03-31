<?php

namespace FCL;

class Capability
{
    /**
     * Админ (нескольких сайтов)
     */
    const ROLE_SUPER_ADMIN  = 'manage_network';
    /**
     * Админ (одного сайта)
     */
    const ROLE_ADMIN        = 'activate_plugins';
    /**
     * Модератор
     */
    const ROLE_EDITOR       = 'moderate_comments';
    /**
     * Автор
     */
    const ROLE_AUTHOR       = 'edit_published_posts';
    /**
     * Редактор
     */
    const ROLE_CONTRIBUTOR  = 'edit_posts';
    /**
     * Читатель
     */
    const ROLE_SUBSCRIBER   = 'read';
}