@if ($status)<x-admin.badge :color="$status->color()">{{ $status->label() }}</x-admin.badge>@endif
