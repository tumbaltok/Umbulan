@props(['url', 'color' => 'primary'])
@php
    // Tentukan warna tema tombol (Sky Blue)
    $buttonColor = '#0284c7';
    $hoverColor = '#0369a1';
@endphp
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 30px auto; text-align: center; width: 100%;">
<tr>
<td align="center">
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; position: relative; -webkit-text-size-adjust: none; border-radius: 14px; color: #fff; display: inline-block; font-size: 14px; font-weight: bold; text-decoration: none; padding: 14px 30px; background-color: {{ $buttonColor }}; border: 1px solid {{ $buttonColor }}; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2);">
    {{ $slot }}
</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
