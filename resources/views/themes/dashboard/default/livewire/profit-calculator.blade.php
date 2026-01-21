<div>
    <div class="card shadow">
        <div class="card-header border-0">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">Profit Calculator</h3>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="number" wire:model="hashpower" step="1" min="1" class="form-control @error('hashpower')is-invalid @enderror" placeholder="Hashpower" wire:keyup.debounce.500ms="calculate">
                            <div class="input-group-append">
                                <div class="input-group-text">{{ setting('hashpower_unit') }}/s</div>
                            </div>
                            @error('hashpower')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="number" readonly wire:model="amount" class="form-control" placeholder="Deposit Amount">
                            <div class="input-group-append">
                                <div class="input-group-text">{{ setting('currency_code') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center table-flush text-center">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Amount</th>
                    <th scope="col">Period</th>
                </tr>
                </thead>
                <tbody>
                @foreach (calculator_periods() as $days)
                    <tr>
                        <td>
                            <span class="profits[{{ $days }}]" wire:model="profits">{{ currency_format($profits[$days] ?? 0, 8) }}</span> {{ setting('currency_code') }}
                        </td>
                        <td>
                            @php
                                $days_class = 'success';
                                if($days >= 7 ): $days_class = 'info'; endif;
                                if($days >= 30 ): $days_class = 'primary'; endif;
                                if($days >= 365 ): $days_class = 'danger'; endif;
                            @endphp
                            <span class="badge badge-{{ $days_class }}">
                                        @if($days < 7)
                                    {{ $days }} {{ $days === '1' ? 'day' : 'days' }}
                                @elseif($days === '7')
                                    1 week
                                @elseif($days === '14')
                                    2 weeks
                                @elseif($days === '21')
                                    3 weeks
                                @elseif($days === '30')
                                    1 month
                                @elseif($days === '60')
                                    2 months
                                @elseif($days === '90')
                                    3 months
                                @elseif($days === '120')
                                    4 months
                                @elseif($days === '150')
                                    5 months
                                @elseif($days === '180')
                                    6 months
                                @elseif($days === '210')
                                    7 months
                                @elseif($days === '240')
                                    8 months
                                @elseif($days === '270')
                                    9 months
                                @elseif($days === '300')
                                    10 months
                                @elseif($days === '330')
                                    11 months
                                @elseif($days === '365')
                                    1 year
                                @else
                                    {{ $days }} days
                                @endif
                                    </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
