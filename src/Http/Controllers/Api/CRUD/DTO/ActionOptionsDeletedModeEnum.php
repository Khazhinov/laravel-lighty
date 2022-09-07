<?php

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO;

enum ActionOptionsDeletedModeEnum: string
{
    /**
     * Загружать без удаленных элементов (режим по умолчанию)
     */
    case WithoutTrashed = 'without_trashed';

    /**
     * Загружать и удаленные, и не удаленные
     */
    case WithTrashed = 'with_trashed';

    /**
     * Загружать только удаленные
     */
    case OnlyTrashed = 'only_trashed';
}
