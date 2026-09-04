@extends('layouts.authenticated')
@section('title','Events · GradConn')
@section('heading','Events')
@section('content')
<div class="page-actions"><a href="{{ route('admin.events_create') }}">Create event</a> <a href="{{ route('admin.admin_archive') }}">Archive</a></div>
<x-social-feed :posts="$posts" :mention-users="$mentionUsers" :manage-events="true" />
@endsection

