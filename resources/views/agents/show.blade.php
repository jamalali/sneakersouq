<x-layout>
    <h1 class="title is-3">
        Agenty Details
    </h1>

    <div>
        <p class="subtitle is-5">
            <strong>{{ $total }}</strong>&nbsp;total products
        </p>
        <form method="POST" action="{{ route('agents.sync', ['agentId' => $agentId]) }}">
            @csrf
            <input type="submit" value="Sync" />
        </form>
    </div>
</x-layout>