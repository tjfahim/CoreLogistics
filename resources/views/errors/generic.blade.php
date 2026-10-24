@include('errors._card', ['code' => $code, 'title' => 'Error', 'color' => '#dc3545', 'text' => (isset($message) && $message) ? $message : 'Something went wrong.'])
