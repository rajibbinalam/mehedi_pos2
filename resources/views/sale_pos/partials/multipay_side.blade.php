<div class="row tw-mb-1">
    <div class="col-md-12 mb-12">
        <strong>@lang('lang_v1.advance_balance'):</strong> <span id="advance_balance_text"></span>
        {!! Form::hidden('advance_balance', null, [
            'id' => 'advance_balance',
            'data-error-msg' => __('lang_v1.required_advance_balance_not_available'),
        ]) !!}
    </div>
    <div class="col-md-12 mb-12">
        
    </div>
    <div class="col-md-5">
        <div class="row">
            <div id="payment_rows_div">
                @php
                    $pos_settings = !empty(session()->get('business.pos_settings')) ? json_decode(session()->get('business.pos_settings'), true) : [];
                    $show_in_pos = '';
                    if ($pos_settings['enable_cash_denomination_on'] == 'all_screens' || $pos_settings['enable_cash_denomination_on'] == 'pos_screen') {
                        $show_in_pos = true;
                    }
                @endphp
                @foreach ($payment_lines as $payment_line)
                    @if ($payment_line['is_return'] == 1)
                        @php
                            $change_return = $payment_line;
                        @endphp

                        @continue
                    @endif

                    @include('sale_pos.partials.payment_row', [
                        'removable' => !$loop->first,
                        'row_index' => $loop->index,
                        'payment_line' => $payment_line,
                        'show_denomination' => true,
                        'show_in_pos' => $show_in_pos,
                        'side_show' => true,
                        'discount' => true,
                    ])
                @endforeach
            </div>
            <input type="hidden" id="payment_row_index" value="{{ count($payment_lines) }}">
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm tw-w-full"
                    id="add-payment-row">@lang('sale.add_payment_row')</button>
            </div>
        </div>
        <br>
        <div class="row @if ($change_return['amount'] == 0) hide @endif payment_row"
            id="change_return_payment_data">
            <div class="col-md-12">
                <div class="box box-solid payment_row bg-lightgray">
                    <div class="box-body">
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('change_return_method', __('lang_v1.change_return_payment_method') . ':*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-money-bill-alt"></i>
                                    </span>
                                    @php
                                        $_payment_method = empty($change_return['method']) && array_key_exists('cash', $payment_types) ? 'cash' : $change_return['method'];

                                        $_payment_types = $payment_types;
                                        if (isset($_payment_types['advance'])) {
                                            unset($_payment_types['advance']);
                                        }
                                    @endphp
                                    {!! Form::select('payment[change_return][method]', $_payment_types, $_payment_method, [
                                        'class' => 'form-control col-md-12 payment_types_dropdown',
                                        'id' => 'change_return_method',
                                        'style' => 'width:100%;',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        @if (!empty($accounts))
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('change_return_account', __('lang_v1.change_return_payment_account') . ':') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fas fa-money-bill-alt"></i>
                                        </span>
                                        {!! Form::select(
                                            'payment[change_return][account_id]',
                                            $accounts,
                                            !empty($change_return['account_id']) ? $change_return['account_id'] : '',
                                            ['class' => 'form-control select2', 'id' => 'change_return_account', 'style' => 'width:100%;'],
                                        ) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="clearfix"></div>
                        @include('sale_pos.partials.payment_type_details', [
                            'payment_line' => $change_return,
                            'row_index' => 'change_return',
                        ])
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('sale_note', __('sale.sell_note') . ':') !!}
                    {!! Form::textarea('sale_note', !empty($transaction) ? $transaction->additional_notes : null, [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => __('sale.sell_note'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('staff_note', __('sale.staff_note') . ':') !!}
                    {!! Form::textarea('staff_note', !empty($transaction) ? $transaction->staff_note : null, [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => __('sale.staff_note'),
                    ]) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="box box-solid">
            <div class="box-body">
                {{-- <div class="col-md-12">
                    <strong>
                        @lang('lang_v1.total_items'):
                    </strong>
                    <br />
                    <span class="lead text-bold total_quantity">0</span>
                </div> --}}

                <div class="col-md-12">
                    {{-- <hr> --}}
                    <strong>
                        @lang('sale.total_payable'):
                    </strong>
                    <br />
                    <span class="lead text-bold total_payable_span">0</span>
                </div>

                <div class="col-md-12">
                    <hr>
                    <strong>
                        @lang('lang_v1.total_paying'):
                    </strong>
                    <br />
                    <span class="lead text-bold total_paying">0</span>
                    <input type="hidden" id="total_paying_input">
                </div>

                <div class="col-md-12">
                    <hr>
                    <strong>
                        @lang('lang_v1.change_return'):
                    </strong>
                    <br />
                    <span class="lead text-bold change_return_span">0</span>
                    {!! Form::hidden('change_return', $change_return['amount'], [
                        'class' => 'form-control change_return input_number',
                        'required',
                        'id' => 'change_return',
                    ]) !!}
                    <!-- <span class="lead text-bold total_quantity">0</span> -->
                    @if (!empty($change_return['id']))
                        <input type="hidden" name="change_return_id"
                            value="{{ $change_return['id'] }}">
                    @endif
                </div>

                <div class="col-md-12">
                    <hr>
                    <strong>
                        @lang('lang_v1.balance'):
                    </strong>
                    <br />
                    <span class="lead text-bold balance_due">0</span>
                    <input type="hidden" id="in_balance_due" value=0>
                </div>



            </div>
            <!-- /.box-body -->
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white text-center" style="width: 100%" id="pos-save">@lang('sale.finalize_payment')</button>
        </div>
    </div>
</div>