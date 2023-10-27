<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Bill;
use App\Models\Treatment;
use App\Models\Client;
use Spipu\Html2Pdf\Html2Pdf;


class BillController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $bill = Bill::orderBy('id', 'desc')->paginate(5);

        return view('bill.index', [
            'user' => $user,
            'bill' => $bill,

        ]);
    }

    public function add()
    {
        $user = Auth::user();
        $treatment = Treatment::all();
        $client = Client::all();

        return view('bill.add', [
            'user' => $user,
            'treatment' => $treatment,
            'client' => $client
        ]);
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $bill = new Bill();

        $attendedby = $request->input('attendedby');
        $client = $request->input('client');
        $treatment = $request->input('treatment');

        $bill->attendedby = $attendedby;
        $bill->client_id = $client;
        $bill->treatment_id = $treatment;
        $bill->users_id = $user_id;

        $bill->save();

        return redirect()->route('bill.index')->with(['message' => 'factura agreagada con exito']);
    }


    public function edit($id)
    {
        $user = Auth::user();
        $client = Client::all();
        $treatment = Treatment::all();
        $bill = Bill::find($id);

        return view('bill.edit', [
            'client' => $client,
            'user' => $user,
            'bill' => $bill,
            'treatment' => $treatment
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $bill = Bill::find($id);

        $attendedby = $request->input('attendedby');
        $client = $request->input('client');
        $treatment = $request->input('treatment');

        $bill->attendedby = $attendedby;
        $bill->client_id = $client;
        $bill->treatment_id = $treatment;

        // var_dump($bill);
        // die();

        if ($user_id == $bill->users_id) {
            DB::table('bills')
                ->where('id', $id)
                ->update([
                    'attendedby' => $attendedby,
                    'client_id' => $client,
                    'treatment_id' => $treatment
                ]);
        }

        return redirect()->route('bill.index')->with(['message' => 'factura actualizada con exito']);
    }



    public function delete($id)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $bill = Bill::find($id);

        if ($user && $user_id == $bill->users_id) {
            $bill->delete();
        }

        return redirect()->route('bill.index')->with(['message' => 'factura eliminada con exito']);
    }


    public function viewpdf($id)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $bill = Bill::find($id);

        $client = Client::all();

        return view('bill.viewpdf', [
            'user' => $user,
            'client' => $client,
            'bill' => $bill
        ]);
    }

    public function printpdf($id)
    {
        $html2pdf = new Html2Pdf();

        $bill = Bill::find($id);

        $html = "<h2>Veterinaria los codornices</h2>";

        $html .= '<h4>Atendido por: </h4>' . $bill->attendedby . '<h4>Cliente:</h4> ' . $bill->client->name .  "<h4>tratamiento: </h4> " . $bill->treatment->name  . "<h4>Monto:</h4>" . " RD$ " . $bill->treatment->amount . "<h3>'Gracias por preferirnos'</h3>";

        $html2pdf->writeHTML($html);
        $html2pdf->output("factura.pdf");
    }
}
