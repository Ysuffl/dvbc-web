<table>
    <thead>
    <tr>
        <th>name</th>
        <th>phone</th>
        <th>gender</th>
        <th>nat</th>
        <th>age_range</th>
        <th>total_spend</th>
        <th>total_visit</th>
        <th>date</th>
        <th>time_in</th>
        <th>time_out</th>
        <th>toal_pax</th>
        <th>pu_din</th>
        <th>pu_fam</th>
        <th>pu_celeb</th>
        <th>pu_party</th>
        <th>pu_corp</th>
        <th>pu_comm</th>
        <th>pr_reg</th>
        <th>pr_ayce</th>
        <th>pr_aycd</th>
        <th>pr_buff</th>
        <th>pr_iftar</th>
        <th>pr_alc</th>
        <th>time_wdd</th>
        <th>time_wdn</th>
        <th>time_wed</th>
        <th>time_wen</th>
    </tr>
    </thead>
    <tbody>
    @foreach($customers as $customer)
        @php
            // Kumpulkan ID Tag unik yang pernah dimiliki customer ini dari seluruh kunjungannya
            $ownedTagIds = [];
            $latestBooking = null;
            if ($customer->bookings) {
                // Sorting descending
                $latestBooking = $customer->bookings->sortByDesc('start_time')->first();
                foreach($customer->bookings as $b) {
                    if ($b->tags) {
                        foreach($b->tags as $t) {
                            $ownedTagIds[] = $t->id;
                        }
                    }
                }
            }
            $ownedTagIds = array_unique($ownedTagIds);
            
            // Map manual nama-nama Tag seperti di excel
            // Kita harus cari ID dari Tag berdasarkan Name-nya karena name di DB fix
            // Supaya tidak query berulang, kita asumsikan array match via collection static list
            // Namun karena kita tidak pasang model cache di view, lebih baik cek aja by name property kalau kita punya all tags?
            // Tapi yang kita tahu di customer->bookings->tags adalah Object. Jadi kumpulkan namanya saja.
            
            $ownedTagNames = [];
            if ($customer->bookings) {
                foreach($customer->bookings as $b) {
                    if ($b->tags) {
                        foreach($b->tags as $t) {
                            $ownedTagNames[] = $t->name;
                        }
                    }
                }
            }
            $ownedTagNames = array_unique($ownedTagNames);
            
            $hasTag = function($tagName) use ($ownedTagNames) {
                return in_array($tagName, $ownedTagNames) ? 1 : 0;
            };

            // Calculate exact total spend correctly
            $totalSpend = (int) ($customer->total_spending + ($customer->bookings ? $customer->bookings->sum('billed_price') : 0));
        @endphp
        <tr>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ strtoupper($customer->gender ?: '') }}</td>
            <td>{{ $customer->nat ?: '' }}</td>
            <td>{{ $customer->age ?: '' }}</td>
            <td>{{ $totalSpend }}</td>
            <td>{{ $customer->total_visits ?: ($customer->visits_count ?? 0) }}</td>
            <td>{{ $latestBooking ? \Carbon\Carbon::parse($latestBooking->start_time)->format('Y-m-d') : '' }}</td>
            <td>{{ $latestBooking ? \Carbon\Carbon::parse($latestBooking->start_time)->format('H:i') : '' }}</td>
            <td>{{ $latestBooking ? \Carbon\Carbon::parse($latestBooking->end_time)->format('H:i') : '' }}</td>
            <td>{{ $latestBooking ? $latestBooking->pax : 1 }}</td>
            
            <td>{{ $hasTag('Dining') }}</td>
            <td>{{ $hasTag('Family') }}</td>
            <td>{{ $hasTag('Celebration') }}</td>
            <td>{{ $hasTag('Party') }}</td>
            <td>{{ $hasTag('Corporate') }}</td>
            <td>{{ $hasTag('Community') }}</td>
            
            <td>{{ $hasTag('Regular F&B') }}</td>
            <td>{{ $hasTag('AYCE') }}</td>
            <td>{{ $hasTag('AYCD') }}</td>
            <td>{{ $hasTag('Buffet') }}</td>
            <td>{{ $hasTag('Iftar Buffet') }}</td>
            <td>{{ $hasTag('Alcohol') }}</td>
            
            <td>{{ $hasTag('Weekday Day') }}</td>
            <td>{{ $hasTag('Weekday Night') }}</td>
            <td>{{ $hasTag('Weekend Day') }}</td>
            <td>{{ $hasTag('Weekend Night') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
