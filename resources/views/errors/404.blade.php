@extends('errors::minimal')

@section('title', 'Page Not Found')

@section('code', '404')

@section('message', __($exception->getMessage() ?: 'Page Not Found'))