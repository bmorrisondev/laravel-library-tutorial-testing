<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(): View {
        return view('books.index', ['books' => Book::all()]);
    }
}
