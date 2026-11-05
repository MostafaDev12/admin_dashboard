@extends('errors::minimal')
@section('title', app(\App\Services\CmsMessages::class)->get('error_404'))
@section('code', '404')
@section('message', app(\App\Services\CmsMessages::class)->get('error_404'))
