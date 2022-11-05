@extends('backend.layouts.app')

@section('title', ' Section Settings ')


@section('content')

<div class="row justify-content-center">
  <div class="col-md-10">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          Manage sections
        </h3>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-4 col-sm-3">
            <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
              <a class="nav-link active" id="vert-tabs-One-tab" data-toggle="pill" href="#vert-tabs-One" role="tab"
                aria-controls="vert-tabs-One" aria-selected="true">Section One</a>
              <a class="nav-link" id="vert-tabs-Two-tab" data-toggle="pill" href="#vert-tabs-Two" role="tab"
                aria-controls="vert-tabs-Two" aria-selected="false">Section Two</a>
              <a class="nav-link" id="vert-tabs-Three-tab" data-toggle="pill" href="#vert-tabs-Three" role="tab"
                aria-controls="vert-tabs-Three" aria-selected="false">Section Three</a>
              <a class="nav-link" id="vert-tabs-four-tab" data-toggle="pill" href="#vert-tabs-four" role="tab"
                aria-controls="vert-tabs-four" aria-selected="false">Section Four</a>
              <a class="nav-link" id="vert-tabs-five-tab" data-toggle="pill" href="#vert-tabs-five" role="tab"
                aria-controls="vert-tabs-five" aria-selected="false">Section Five</a>
            </div>
          </div>
          <div class="col-8 col-sm-9">
            <div class="tab-content" id="vert-tabs-tabContent">

              <div class="tab-pane fade show active" id="vert-tabs-One" role="tabpanel"
                aria-labelledby="vert-tabs-One-tab">
                @include("backend.content.settings.manage-sections.includes.sectionOne")
              </div>

              <div class="tab-pane fade" id="vert-tabs-Two" role="tabpanel" aria-labelledby="vert-tabs-Two-tab">
                @include("backend.content.settings.manage-sections.includes.sectionTwo")
              </div>

              <div class="tab-pane fade" id="vert-tabs-Three" role="tabpanel" aria-labelledby="vert-tabs-Three-tab">
                @include("backend.content.settings.manage-sections.includes.sectionThree")
              </div>

              <div class="tab-pane fade" id="vert-tabs-four" role="tabpanel" aria-labelledby="vert-tabs-four-tab">
                @include("backend.content.settings.manage-sections.includes.sectionFour")
              </div>

              <div class="tab-pane fade" id="vert-tabs-five" role="tabpanel" aria-labelledby="vert-tabs-five-tab">
                @include("backend.content.settings.manage-sections.includes.sectionFive")
              </div>

            </div>
          </div>
        </div>
      </div>
      <!-- /.card -->
    </div>
  </div> <!-- col -->


</div> <!-- .row -->

@endsection