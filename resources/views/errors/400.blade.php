@props([
'code' => '400',
'title' => 'Ungültige Anfrage',
'subtitle' => $exception->getMessage(),
'icon' => 'tabler-exclamation-circle'
])

@extends('errors::layout')