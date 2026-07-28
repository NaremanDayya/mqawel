<x-filament-panels::page>
    <div class="bg-white shadow rounded-lg p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center border-b pb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-700">{{__('backend.invoice')}}</h1>
                <p class="text-gray-500">{{__('backend.number')}} #{{$sale->invoice_number}}</p>
                <p class="text-gray-500">{{__('backend.date')}}: {{$sale->sale_date}}</p>
            </div>

            <div>
                <img src="{{asset('images/watermark-blue.png')}}" class="h-5"/>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4">
            <div>
                <h2 class="font-bold text-gray-700">{{__('backend.billed_to')}}</h2>
                <p class="text-gray-600">{{$sale->patient ? $sale->patient->name : '--'}}</p>
                <p class="text-gray-600">{{$sale->patient ? $sale->patient->address : '--'}}</p>
                <p class="text-gray-600">{{$sale->patient ? $sale->patient->email : '--'}}</p>
                <p class="text-gray-600">{{$sale->patient ? $sale->patient->phone : '--'}}</p>
            </div>

            <div class="text-{{true ? 'left' : 'right'}}">
                <h2 class="font-bold text-gray-700">{{__('backend.pharmacy')}}</h2>
                <p class="text-gray-600">{{Auth::user()->pharmacy->name}}</p>
                <p class="text-gray-600">{{Auth::user()->pharmacy->address}}</p>
                <p class="text-gray-600">{{Auth::user()->pharmacy->email}}</p>
                <p class="text-gray-600">{{Auth::user()->pharmacy->phone}}</p>
            </div>
        </div>

        <table class="w-full mt-6 border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{__('backend.description')}}</th>
                    <th class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{__('backend.quantity')}}</th>
                    <th class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{__('backend.unit_price')}}</th>
                    <th class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{__('backend.total_price')}}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($sale->items as $SaleItem)
                @php
                    $name= $SaleItem->item->drug->commercial_name;
                    $quantity= $SaleItem->quantity;
                    $unit_price= $SaleItem->unit_price;
                    $total_price= $quantity * $unit_price;
                @endphp
                <tr>
                    <td class="border border-gray-200 p-2 text-gray-700">{{$name}}</td>
                    <td class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{$quantity}}</td>
                    <td class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{number_format($unit_price)}}</td>
                    <td class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{number_format($total_price)}}</td>
                </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr class="bg-gray-100">
                    <td colspan="3" class="border border-gray-200 p-2 text-{{true ? 'left' : 'right'}} font-bold text-gray-700">{{__('backend.subtotal')}}</td>
                    <td class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{number_format($sale->total_amount)}}</td>
                </tr>
                <tr class="bg-gray-100-">
                    <td colspan="3" class="border border-gray-200 p-2 text-{{true ? 'left' : 'right'}} font-bold text-gray-700">{{__('backend.discount')}}</td>
                    <td class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{number_format($sale->discount_amount)}}</td>
                </tr>
                <tr class="bg-gray-100-">
                    <td colspan="3" class="border border-gray-200 p-2 text-{{true ? 'left' : 'right'}} font-bold text-gray-700">{{__('backend.tax')}}</td>
                    <td class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700">{{number_format($sale->tax_amount)}}</td>
                </tr>
                <tr class="bg-gray-100-">
                    <td colspan="3" class="border border-gray-200 p-2 text-{{true ? 'left' : 'right'}} font-bold text-gray-700">{{__('backend.total')}}</td>
                    <td class="border border-gray-200 p-2 text-{{true ? 'right' : 'left'}} text-gray-700 font-semibold">{{number_format($sale->net_amount)}}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-filament-panels::page>
