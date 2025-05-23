@props([
'code' => '404',
'title' => 'Nicht gefunden',
'subtitle' => $exception->getMessage() ?? 'Die angeforderte Ressource wurde nicht gefunden.',
'icon' => 'tabler-zoom-question'
])

@extends('errors::layout')
