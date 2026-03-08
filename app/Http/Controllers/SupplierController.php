<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('dashboard.suppliers.index',compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate(['name'=>'required|unique:suppliers,name']);

        Supplier::create([
            'name'=>$request->name,
            'phone'=>$request->phone,
            'email'=>$request->email,
            'address'=>$request->address,
            'is_active'=>$request->has('is_active'),
        ]);

        return redirect()->route('suppliers.index')->with('success','Supplier created');
    }

    public function update(Request $request,$id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'name'=>'required|unique:suppliers,name,'.$supplier->id
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')->with('success','Supplier updated');
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return redirect()->route('suppliers.index')->with('success','Supplier deleted');
    }
}
