<!-- Tos Modal -->
<div class="modal fade" id="faqsModal" tabindex="-1" aria-labelledby="faqsModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faqsModal">F.A.Q.s</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @foreach ($faqs as $faq)
                    <h4>{{ $faq->question }}</h4>
                    {!! $faq->answer !!}
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>