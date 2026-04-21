import { useState } from "react";

const WorkdayStep = ({ onNext, initialData = {} }) => {
    const [formData, setFormData] = useState({
        workingDays: initialData.workingDays || {
            mon: "8", tue: "8", wed: "8", thu: "8", fri: "8", sat: "0", sun: "0"
        },
        workingHours: initialData.workingHours || { start: "09:00", end: "17:00" }
    });

    const days = [
        { key: "mon", label: "Monday" },
        { key: "tue", label: "Tuesday" },
        { key: "wed", label: "Wednesday" },
        { key: "thu", label: "Thursday" },
        { key: "fri", label: "Friday" },
        { key: "sat", label: "Saturday" },
        { key: "sun", label: "Sunday" }
    ];

    const handleDayToggle = (day, value) => {
        setFormData(prev => ({
            ...prev,
            workingDays: { ...prev.workingDays, [day]: value }
        }));
    };

    const handleSubmit = e => {
        e.preventDefault();
        onNext(formData);
    };

    const DayOption = ({ dayKey, value, label }) => {
        const selected = formData.workingDays[dayKey] === value;
        const isNonWorking = value === "0";
        let selectedClass = "bg-blue-500 text-white border-blue-500";
        if (selected && isNonWorking) selectedClass = "bg-red-500 text-white border-red-500";
        return (
            <label className="inline-block">
                <input type="radio" name={`day-${dayKey}`} checked={selected} onChange={() => handleDayToggle(dayKey, value)} className="hidden" />
                <span
                    className={`inline-block px-5 py-3 rounded-full cursor-pointer transition-all duration-200 font-normal text-center border text-sm leading-4 shadow-sm ${selected ? selectedClass : "bg-white text-gray-800 border-gray-300"}`}
                >
                    {label}
                </span>
            </label>
        );
    };

    return (
        <div>
            <div className="max-w-640px mx-auto overflow-visible">
                <h1 className="text-black text-30px font-normal leading-9 text-center m-0 mb-3">
                    Workday Setup
                </h1>
                <p className="text-center text-slate-500 text-base m-0 mb-20 leading-6">
                    Enter you company name and start date.
                </p>

                <form onSubmit={handleSubmit} className="mb-0">
                    <div className="mb-6">
                        <div className="flex flex-col gap-1">
                            {days.map(day => (
                                <div key={day.key} className="flex items-center justify-between py-2">
                                    <div className="font-medium min-w-[100px] text-sm">{day.label}</div>
                                    <div className="flex gap-2">
                                        <DayOption dayKey={day.key} value="8" label="Full Day" />
                                        <DayOption dayKey={day.key} value="4" label="Half Day" />
                                        <DayOption dayKey={day.key} value="0" label="Non-working Day" />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="text-center mt-btn">
                        <button type="submit" className="btn-primary no-underline">Next</button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default WorkdayStep;
