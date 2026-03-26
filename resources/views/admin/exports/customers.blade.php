<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Level</th>
        <th>Gender</th>
        <th>Age</th>
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
            <td>{{ strtoupper($customer->masterLevel->name ?? 'BRONZE') }}</td>
            <td>{{ $customer->gender ?: '-' }}</td>
            <td>{{ $customer->age ?: '-' }}</td>
            <td>{{ $customer->visits_count ?? 0 }}</td>
            <td>{{ $customer->total_spent ?? 0 }}</td>
            <td>{{ $customer->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
