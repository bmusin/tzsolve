@extends('layouts.app')

@section('title', 'Success')
@section('content')
<?= $message ?>

<script>
  setTimeout( () => { window.location.href = "<?= route('home') ?>" }, 3000)
</script>
@endsection
