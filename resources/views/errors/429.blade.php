@extends('errors.layout')

@section('title', 'Too Many Requests')
@section('code', '429')
@section('tone', 'warning')
@section('glyph', '⏱')
@section('heading', 'Too Many Requests')
@section('message', 'You have made too many requests in a short time. Please wait a moment and try again.')
