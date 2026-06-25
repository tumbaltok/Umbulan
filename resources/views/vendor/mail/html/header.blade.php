@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{{-- @if (trim($slot) === 'Laravel') --}}
<img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjXuNG_5rVue-tYsQgv5uiSGoECEC1vAxxWpjpWWp4eYBFYe2IEF65Ub9LNRvhgbspfUQF1Gy3ySvZ6_f1B5po4_gnCibFE9pHyJijmtzK0-p7HePtXGWmtu96BJPsXuhM191L6xX7AcaGAnssDs5sk0sGidh8EJFxZJk-0j1N8ilJgQAJi4VmXn4d8W2sM/s1600/iconfav.png" class="logo" alt="META">
{{-- <img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo"> --}}
{{-- @else
{!! $slot !!}
@endif --}}
</a>
</td>
</tr>
