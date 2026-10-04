<div id="dynamic-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="modalSize">
        <div class="modal-content rounded-xl p-0 b-0">
            <div class="panel panel-color panel-primary">
                <div class="modal-header">
                    <h5 class="modal-title" id="odal-title">{{ ___('alert.Title') }}</h5>
                    <button type="button" class="close fs-25" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true" class="fs-25">&times;</span> </button>
                </div>
                <div class="modal-body">


                    <div id="modal-loader" class="text-center">
                        <div className="spinner-grow spinner-grow-sm" role="status">
                            <img src="{{ asset('backend/icons/icon/load-indicator-1.gif') }}" alt="" class="w-100">
                            {{-- <span className="sr-only">{{ ___('alert.loading') }}</span> --}}
                        </div>

                    </div>

                    <div id="modal-main-content"> </div>
                </div>

            </div>
        </div>
    </div>
</div>
