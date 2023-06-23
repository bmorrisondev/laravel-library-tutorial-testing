<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(): View {
        return view('loans.index', ['loans' => Auth::user()->activeLoans()]);
    }

    public function store(Book $book, Request $request): RedirectResponse {
        $validator = ValidatorFacade::make($request->all(), [
            'number_borrowed' => 'required|int',
            'return_date'     => 'required',
        ]);

        $validator->after(function (Validator $validator) use ($book) {
            $numberBorrowed = $validator->safe()->number_borrowed;
            $availableCopies = $book->availableCopies();
            if ($numberBorrowed > $availableCopies) {
                $validator->errors()->add(
                    'number_borrowed',
                    "You cannot borrow more than {$availableCopies} book(s)"
                );
            }
        });

        if ($validator->fails()) {
            return to_route('loans.create', ['book' => $book])
                ->withErrors($validator)
                ->withInput();
        }

        $loanDetails = $validator->safe()->only([
            'number_borrowed',
            'return_date',
        ]);

        $loanDetails['book_id'] = $book->id;
        $loanDetails['user_id'] = Auth::user()->id;

        Loan::create($loanDetails);

        return to_route('loans.index')
            ->with('status', 'Book borrowed successfully');
    }

    public function create(Book $book): View {
        return view('loans.create', ['book' => $book]);
    }

    public function terminate(Loan $loan): RedirectResponse {
        $loan->terminate();

        return to_route('loans.index')
            ->with('status', 'Book returned successfully');
    }
}
