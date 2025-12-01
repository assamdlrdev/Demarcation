import React, { useEffect, useState } from 'react';

export default function Users() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
    fetch('/api/users')
        .then(res => {
            console.log(res);
            return res.json();
        })
        .then(data => setUsers(data))
        .catch(err => console.error(err));
}, []);


    if (loading) return <p>Loading users...</p>;

    return (
        <div className="p-5">
            <h1 className="text-xl font-bold mb-4">Users List</h1>
            <ul>
                {users.map(user => (
                    <li key={user.id}>
                        {user.name} - {user.email}
                    </li>
                ))}
            </ul>
        </div>
    );
}
