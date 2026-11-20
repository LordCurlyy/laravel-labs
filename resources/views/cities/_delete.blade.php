<form method="post" action="{{ route('cities.destroy', $city) }}" class="mt-2" data-confirm-delete>
    @csrf @method('DELETE')
    <button class="btn btn-outline-danger w-100" type="submit">Удалить</button>
</form>
