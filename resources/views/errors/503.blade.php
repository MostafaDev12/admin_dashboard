@extends('errors::minimal')
@section('title', app(\App\Services\CmsMessages::class)->get('error_503'))
@section('code', '503')
@section('message', app(\App\Services\CmsMessages::class)->get('error_503'))
