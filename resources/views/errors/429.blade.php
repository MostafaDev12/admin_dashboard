@extends('errors::minimal')
@section('title', app(\App\Services\CmsMessages::class)->get('error_429'))
@section('code', '429')
@section('message', app(\App\Services\CmsMessages::class)->get('error_429'))
