@extends('layouts.authenticated')
@section('title','Events · GradConn')
@section('heading','Event Feed')
@section('content')
<div class="page-actions"><a href="{{ route('alumni_officer.events_create') }}">Create event</a> <a href="{{ route('alumni_officer.archive') }}">Archive</a></div>
<x-social-feed :posts="$posts" :mention-users="$mentionUsers" :manage-events="true" />
@endsection

