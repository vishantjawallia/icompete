
{{ get_setting('title') }}
@if($data['title'])
{{ $data['title'] }}
@endif
{!! nl2br(strip_tags($data['message'])) !!}

Need help?
Contact Support: [{{ get_setting('email') }}]

© {{ date('Y') }} {{ get_setting('name') }}. All Rights Reserved.
