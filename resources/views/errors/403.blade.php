@props([
'code' => '403',
'title' => 'Verboten',
'subtitle' => $exception->getMessage(),
'icon' => 'tabler-exclamation-circle'
])

@extends('errors::layout')