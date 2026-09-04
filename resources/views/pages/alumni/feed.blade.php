@extends('layouts.authenticated')
@section('title','Feed · GradConn')
@section('heading','Community Feed')
@section('content')
<x-social-feed :posts="$posts" :mention-users="$mentionUsers" />
@endsection

