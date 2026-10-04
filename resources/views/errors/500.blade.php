@extends('errors.layout')

@section('title', 'Server Error')
@section('code', '500')
@section('tone', 'danger')
@section('glyph', '⚡')
@section('heading', 'Something Went Wrong')
@section('message', 'An unexpected error occurred on our side. The team has been notified — please try again shortly.')
