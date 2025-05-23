@props([
'code' => '500',
'title' => 'Serverfehler',
'subtitle' => fn () => auth()->user()?->isRootAdmin() ? $exception->getMessage() : 'Etwas ist schief gelaufen.',
'icon' => 'tabler-bug'
])

@extends('errors::layout')