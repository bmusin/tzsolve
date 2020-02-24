@extends('layouts.app')

@section('title', 'Requests')
@section('content')
  <?php if (!$results) : ?>
    <p>No requests have been submitted yet.</p>
  <?php endif ?>
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
        <?php foreach ($results as $request) : ?>
          <tr>
            <td><?= $request['id'] ?></td>
            <td><?= $request['subject'] ?></td>
            <td><?= $request['description'] ?></td>
            <td><?= $request['name'] ?></td>
            <td><?= $request['email'] ?></td>
            <td>
              <?php if (isset($request['download_link'])) : ?>
                <a href="<?= $request['download_link'] ?>"><?= $request['filename'] ?></a>
              <?php endif ?>
            </td>
            <td><?= $request['time'] ?></td>
          </tr>
        <?php endforeach ?>
    </tbody>
  </table>
  <?= $results->links('vendor.pagination.default') ?>

  <hr>
  <p>e-mail address of manager: <?= $email ?>.</p>
  <form action="<?= route('set-manager-email') ?>" method="post">
    <?= csrf_field() ?>
    <input type="email" name="email">
    <input type="submit" value="Set e-mail">
  </form>
@endsection
