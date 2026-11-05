@extends('errors::minimal')
@section('title', app(\App\Services\CmsMessages::class)->get('error_500'))
@section('code', '500')
@section('message', app(\App\Services\CmsMessages::class)->get('error_500'))
