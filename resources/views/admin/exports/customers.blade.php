<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Category</th>
        <th>Total Visits</th>
        <th>Total Spend</th>
        <th>Created At</th>
    </tr>
    </thead>
    <tbody>
    @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->id }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ strtoupper($customer->category ?? 'REGULER') }}</td>
            <td>{{ $customer->bookings_count }}</td>
            <td>{{ $customer->total_spent ?? 0 }}</td>
            <td>{{ $customer->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
