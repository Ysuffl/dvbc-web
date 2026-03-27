<table>
    <thead>
    <tr>
        <th>name</th>
        <th>phone</th>
        <th>age</th>
        <th>gender</th>
        <th>total_spending</th>
        <th>total_visits</th>
    </tr>
    </thead>
    <tbody>
    @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->age ?: '' }}</td>
            <td>{{ strtoupper($customer->gender ?: '') }}</td>
            <td>{{ (int)$customer->total_spending }}</td>
            <td>{{ $customer->total_visits ?: ($customer->visits_count ?? 0) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
