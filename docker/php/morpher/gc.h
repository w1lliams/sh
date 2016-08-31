#ifndef STDPLUS_GC_H
#define STDPLUS_GC_H

// -----------------------------------------------
//  A Very Simple (Yet Useful!) Garbage Collector
// -----------------------------------------------

#include <assert.h>

namespace GC
{

    // ---------------------------------------------------------------------
    class Object
    {
        mutable unsigned refCount;

    public:

        Object () : refCount (0) {}
        Object (const Object &) : refCount (0) {} // refCount is not copied

        const Object & operator = (const Object & ) {
            return *this; // refCount is left unchanged
        }

        virtual ~Object () {
            assert (refCount == 0);
        }


    private: friend class PtrBase;

        void AddRef  () const { 
            ++refCount; 
        }

        void Release () const {
            if (--refCount == 0) {
                delete this; // calls the virtual destructor
            }
        }
    };


    // PtrBase is just used to limit access to Object's AddRef and Release methods
    // so that no one would call them unintentionally (I know this is paranoid ;)
    class PtrBase {
    protected:
        static void AddRef  (const Object * p) {p->AddRef  ();}
        static void Release (const Object * p) {p->Release ();}
    };

    // ---------------------------------------------------------------------
    template <class T> class Ptr : public PtrBase
    {
        T * p; 

    public:

        T * GetP () const {return p;} // used to access Ptr <T2>::p

        T & operator * () const { return *p; }
        T * operator -> () const { return p; }    
        operator bool () const { return p != 0; }
        bool operator ! () const { return p == 0; }

        Ptr () : p (0) {}

        // конструктор из указателя
        Ptr (T * rp) : p (rp) { 
            if (p) AddRef (p); 
        }

        // конструктор из Ptr другого типа
        template <class T2> Ptr (const Ptr <T2> & r) : p (r.GetP ()) {
            if (p) AddRef (p); 
        }

        // конструктор копии
        // по идее, является частным случаем предыдущего конструктора,
        // но компилятор почему-то не использует его, а генерит
        // конструктор копии по умолчанию ('memberwise')
        // ну и фиг с ним ;)
        Ptr (const Ptr & r) : p (r.p) {
            if (p) AddRef (p); 
        }

        Ptr & operator = (T * rp) {
            // it is safe to assign a pointer to itself
            // provided that AddRef () goes before Release ()
            if (rp) AddRef (rp);
            if (p)  Release (p);
            p = rp;
            return *this;
        }

        template <class T2> 
        Ptr & operator = (const Ptr <T2> & r) {
            return operator = (r.GetP ());
        }

        Ptr & operator = (const Ptr & r) {
            return operator = (r.p);
        }

        ~Ptr () {
            if (p) Release (p);
        }

        // wouldn't it be more useful 
        // to compare the pointed-to values here?
        bool operator == (const Ptr & r) { return p == r.p; } 
        bool operator != (const Ptr & r) { return p != r.p; } 
        // other pointer arithmetics are not supported,
        // as a matter of principle :)
    };

}; // namespace GC

#endif // STDPLUS_GC_H
