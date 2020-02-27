@extends('layouts.app')

@section('title', 'Feedback')
@section('content')
<form class='centered' action="<?= route('feedbacks.store') ?>" enctype="multipart/form-data" method="post">
  <?= csrf_field() ?>
  <table>
    <tbody>
      <tr>
        <td>
          <label for="subject">Subject</label><br>
          <input type="text" name="subject" id="subject"/>
        </td>
      </tr>
      <tr>
        <td>
          <label for="description">Description</label><br>
          <textarea name="description" cols="32"></textarea><br>
        </td>
      </tr>
      <tr>
        <td>
          <label for="file">File to upload</label><br>
          <input type="file" name="file" id="file"><br>
        </td>
      </tr>
      <tr>
        <td>
          <input type="submit" value="Send request"/>
        </td>
      </tr>
    </tbody>
  </table>
</form>
<form action="<?= url('logout') ?>" method="post">
  <?= csrf_field() ?>
  <input type="submit" value="Log out">
</form>
@endsection
