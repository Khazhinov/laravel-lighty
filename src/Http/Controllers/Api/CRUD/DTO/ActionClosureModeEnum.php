<?php

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO;

enum ActionClosureModeEnum: string
{
    case Builder = 'builder';
    case Filter = 'filter';
    case BeforeFilling = 'before_filling';
    case AfterFilling = 'after_filling';
    case BeforeDeleting = 'before_deleting';
    case AfterDeleting = 'after_deleting';
    case AfterSave = 'after_save';
}
