@extends('layouts.app')

@section('title', 'Requests')
@section('content')
  @if (empty($results))
    <p>No requests have been submitted yet.</p>
  @else
      <table>
        <caption>Requests</caption>
        <thead>
          <tr>
            <th>ID</th>
            <th>Subject</th>
            <th>Description</th>
            <th>Client&#39;s name</th>
            <th>Client&#39;s e-mail</th>
            <th>Link to file</th>
            <th>When request was made</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($results as $request)
              <tr>
                <td>{{ $request['id'] }}</td>
                <td>{{ $request['subject'] }}</td>
                <td>{{ $request['description'] }}</td>
                <td>{{ $request['name'] }}</td>
                <td>{{ $request['email'] }}</td>
                <td>
                  @if (isset($request['att_link']))
                    <a href="{{ $request['att_link'] }}">{{ $request['att_name'] }}</a>
                  @endif
                </td>
                <td>{{ $request['time'] }}</td>
              </tr>
            @endforeach
        </tbody>
      </table>
  {{ $results->links('vendor.pagination.default') }}
  @endif

  <hr>
  <p>e-mail address of manager: {{ $email }}.</p>
  <form action="{{ route('manager-email.update') }}" method="post">
    @method('PUT')
    @csrf
    <input type="email" name="email">
    <input type="submit" value="Set e-mail">
  </form>
  <hr>
  <form action="{{ route('requests.truncate') }}" method="post">
      @method('DELETE')
      @csrf
      <input type="submit" value="Remove all requests">
  </form>
  <hr>
  <form action="{{ url('logout') }}" method="post">
      @csrf
      <input type="submit" value="Log out">
  </form>
@endsection
