@include('errors._card', ['code' => 500, 'title' => 'Server Error', 'color' => '#dc3545', 'text' => (isset($message) && $message) ? $message : 'Something went wrong on our side. Please try again.'])
