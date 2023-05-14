@extends('backend.layouts.app')

@section('title', 'Sales')

@section('styles')
<style>
    .w-100 {
        min-width: 100px;
    }
</style>
@endsection

@section('content')
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-sm-5">
          <h4 class="my-1">
            @lang('Sales')
          </h4>
        </div> <!-- col-->
        <div class="col-sm-7 pull-right">
          {{-- @include('backend.content.order.includes.header-buttons') --}}
        </div> <!-- col-->
      </div> <!-- row-->
    </div>
    <div class="card-body">
      @livewire('sales-table')
    </div> <!-- card-body-->
  </div> <!-- card-->
@endsection

@push('after-styles')
  @livewireStyles
@endpush

@push('after-scripts')
  @livewireScripts
  <script>
     const popupCenter = ({url, title, w, h}) => {        // Fixes dual-screen position                             Most browsers      Firefox
        const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
        const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
        const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        const systemZoom = width / window.screen.availWidth;
        const left = (width - w) / 2 / systemZoom + dualScreenLeft
        const top = (height - h) / 2 / systemZoom + dualScreenTop
        const newWindow = window.open(url, title, `scrollbars=yes, width=${w / systemZoom}, height=${h / systemZoom}, top=${top},left=${left}`)
        if (window.focus) newWindow.focus();
     }

     $(function () {
        $(document).on('click', '.btn_details', function (event) {
           event.preventDefault();
           var href = $(this).attr('href');
           popupCenter({url: href, title: 'Print Order', w: 1080, h: 720});
        });
     });
  </script>
@endpush
