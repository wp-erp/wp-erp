import { useState, useEffect } from "react";
import { getLeaveTypes } from "../../utils/api";

const makeDefaultLeaveYear = () => {
    const year = new Date().getFullYear();
    return { id: Date.now(), fy_name: String(year), start_date: `${year}-01-01`, end_date: `${year}-12-31` };
};

const LeaveStep = ({ onNext, initialData = {} }) => {
    const [errors, setErrors] = useState([]);
    const [leaveTypes, setLeaveTypes] = useState([]);
    const [loadingTypes, setLoadingTypes] = useState(true);
    const [formData, setFormData] = useState({
        enableLeaveManagement: initialData.enableLeaveManagement ?? true,
        selectedLeaveTypes: initialData.selectedLeaveTypes || [],
        leaveYears:
            initialData.leaveYears && initialData.leaveYears.length > 0
                ? initialData.leaveYears.map(fy => ({
                      id: fy.id || Date.now() + Math.random(),
                      fy_name: fy.fy_name || "",
                      start_date: fy.start_date || "",
                      end_date: fy.end_date || ""
                  }))
                : [makeDefaultLeaveYear()]
    });

    useEffect(() => {
        getLeaveTypes()
            .then(res => {
                setLeaveTypes(res.data);
                // Default: select all if none previously selected
                if (
                    !initialData.selectedLeaveTypes ||
                    initialData.selectedLeaveTypes.length === 0
                ) {
                    setFormData(prev => ({
                        ...prev,
                        selectedLeaveTypes: res.data.map(t => t.id)
                    }));
                }
            })
            .catch(() => {})
            .finally(() => setLoadingTypes(false));
    }, []);

    useEffect(() => {
        if (initialData.leaveYears && initialData.leaveYears.length > 0) {
            setFormData(prev => ({
                ...prev,
                leaveYears: initialData.leaveYears.map(fy => ({
                    id: fy.id || Date.now() + Math.random(),
                    fy_name: fy.fy_name || "",
                    start_date: fy.start_date || "",
                    end_date: fy.end_date || ""
                }))
            }));
        } else if (initialData.leaveYears !== undefined) {
            setFormData(prev => ({
                ...prev,
                leaveYears: [makeDefaultLeaveYear()]
            }));
        }
    }, [initialData.leaveYears]);

    const toggleLeaveType = id => {
        setFormData(prev => ({
            ...prev,
            selectedLeaveTypes: prev.selectedLeaveTypes.includes(id)
                ? prev.selectedLeaveTypes.filter(t => t !== id)
                : [...prev.selectedLeaveTypes, id]
        }));
    };

    const toggleAll = () => {
        const allSelected =
            formData.selectedLeaveTypes.length === leaveTypes.length;
        setFormData(prev => ({
            ...prev,
            selectedLeaveTypes: allSelected ? [] : leaveTypes.map(t => t.id)
        }));
    };

    const handleToggle = field => {
        setFormData(prev => ({ ...prev, [field]: !prev[field] }));
    };

    const handleLeaveYearChange = (id, field, value) => {
        setFormData(prev => ({
            ...prev,
            leaveYears: prev.leaveYears.map(year =>
                year.id === id ? { ...year, [field]: value } : year
            )
        }));
    };

    const addLeaveYear = () => {
        setFormData(prev => ({
            ...prev,
            leaveYears: [
                ...prev.leaveYears,
                { id: Date.now(), fy_name: "", start_date: "", end_date: "" }
            ]
        }));
    };

    const removeLeaveYear = id => {
        if (formData.leaveYears.length > 1) {
            setFormData(prev => ({
                ...prev,
                leaveYears: prev.leaveYears.filter(year => year.id !== id)
            }));
        }
    };

    const validate = () => {
        const errs = [];
        const yearNames = [];
        formData.leaveYears.forEach((year, index) => {
            const row = index + 1;
            if (!year.fy_name?.trim())
                errs.push(`Please give a financial year name on row #${row}`);
            if (!year.start_date)
                errs.push(
                    `Please give a financial year start date on row #${row}`
                );
            if (!year.end_date)
                errs.push(
                    `Please give a financial year end date on row #${row}`
                );
            if (
                year.start_date &&
                year.end_date &&
                new Date(year.end_date) <= new Date(year.start_date)
            ) {
                errs.push(
                    `End date must be greater than the start date on row #${row}`
                );
            }
            if (year.fy_name?.trim()) {
                if (yearNames.includes(year.fy_name.trim())) {
                    errs.push(
                        `Duplicate financial year name "${year.fy_name}" on row #${row}`
                    );
                } else {
                    yearNames.push(year.fy_name.trim());
                }
            }
        });
        return errs;
    };

    const handleSubmit = e => {
        e.preventDefault();
        const validationErrors = validate();
        if (validationErrors.length > 0) {
            setErrors(validationErrors);
            window.scrollTo({ top: 0, behavior: "smooth" });
            return;
        }
        setErrors([]);
        onNext(formData);
    };

    const allSelected =
        leaveTypes.length > 0 &&
        formData.selectedLeaveTypes.length === leaveTypes.length;

    return (
        <div>
            <div className="max-w-640px mx-auto overflow-visible">
                <h1 className="text-black text-30px font-normal leading-9 text-center m-0 mb-3">
                    Leave Setup
                </h1>
                <p className="text-center text-slate-500 text-base m-0 mb-16 leading-6">
                    Configure leave years and select leave types for your
                    organization.
                </p>

                <form onSubmit={handleSubmit} className="mb-0">
                    {errors.length > 0 && (
                        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                            <div className="flex items-start">
                                <svg
                                    className="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                                <div className="flex-1">
                                    <h3 className="text-sm font-medium text-red-800 mb-2">
                                        Please fix the following errors:
                                    </h3>
                                    <ul className="list-disc list-inside text-sm text-red-700 space-y-1">
                                        {errors.map((error, index) => (
                                            <li key={index}>{error}</li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Leave Years */}
                    <div className="mb-16">
                        {formData.leaveYears.map(leaveYear => (
                            <div key={leaveYear.id} className="mb-4">
                                <div className="flex gap-3 items-end">
                                    <div className="flex-1">
                                        <label
                                            htmlFor={`leaveYear-${leaveYear.id}`}
                                            className="block text-xs font-medium text-gray-600 mb-1"
                                        >
                                            Leave Year
                                        </label>
                                        <input
                                            type="text"
                                            id={`leaveYear-${leaveYear.id}`}
                                            value={leaveYear.fy_name}
                                            onChange={e =>
                                                handleLeaveYearChange(
                                                    leaveYear.id,
                                                    "fy_name",
                                                    e.target.value
                                                )
                                            }
                                            className="input py-2 px-3 text-sm"
                                            placeholder="e.g. 2025"
                                        />
                                    </div>
                                    <div className="flex-1">
                                        <label
                                            htmlFor={`startDate-${leaveYear.id}`}
                                            className="block text-xs font-medium text-gray-600 mb-1"
                                        >
                                            Start Date
                                        </label>
                                        <input
                                            type="date"
                                            id={`startDate-${leaveYear.id}`}
                                            value={leaveYear.start_date}
                                            onChange={e =>
                                                handleLeaveYearChange(
                                                    leaveYear.id,
                                                    "start_date",
                                                    e.target.value
                                                )
                                            }
                                            className="input py-2 px-3 text-sm"
                                        />
                                    </div>
                                    <div className="flex-1">
                                        <label
                                            htmlFor={`endDate-${leaveYear.id}`}
                                            className="block text-xs font-medium text-gray-600 mb-1"
                                        >
                                            End Date
                                        </label>
                                        <input
                                            type="date"
                                            id={`endDate-${leaveYear.id}`}
                                            value={leaveYear.end_date}
                                            onChange={e =>
                                                handleLeaveYearChange(
                                                    leaveYear.id,
                                                    "end_date",
                                                    e.target.value
                                                )
                                            }
                                            className="input py-2 px-3 text-sm"
                                        />
                                    </div>
                                    {formData.leaveYears.length > 1 && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                removeLeaveYear(leaveYear.id)
                                            }
                                            className="flex-shrink-0 w-9 h-10 flex items-center justify-center border border-red-400 rounded-md text-red-400 hover:bg-red-50 transition-colors"
                                        >
                                            <svg
                                                width="14"
                                                height="16"
                                                viewBox="0 0 15 18"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M9.20414 6.69231L8.93787 13.6154M5.25444 13.6154L4.98817 6.69231M12.6559 4.22351C12.9189 4.26324 13.1811 4.30575 13.4423 4.35099M12.6559 4.22351L11.8345 14.902C11.7651 15.8037 11.0132 16.5 10.1088 16.5H4.08352C3.17913 16.5 2.42721 15.8037 2.35784 14.902L1.53642 4.22351M12.6559 4.22351C11.774 4.09034 10.8819 3.98835 9.98077 3.91871M0.75 4.35099C1.01121 4.30575 1.27336 4.26324 1.53642 4.22351M1.53642 4.22351C2.41829 4.09034 3.31038 3.98835 4.21154 3.91871M9.98077 3.91871V3.21399C9.98077 2.30679 9.28027 1.54941 8.37353 1.5204C7.9494 1.50684 7.52358 1.5 7.09615 1.5C6.66873 1.5 6.24291 1.50684 5.81878 1.5204C4.91204 1.54941 4.21154 2.30679 4.21154 3.21399V3.91871M9.98077 3.91871C9.02889 3.84515 8.0669 3.80769 7.09615 3.80769C6.12541 3.80769 5.16342 3.84515 4.21154 3.91871"
                                                    stroke="#EF4444"
                                                    strokeWidth="1.5"
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                />
                                            </svg>
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}

                        <button
                            type="button"
                            onClick={addLeaveYear}
                            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 transition-colors"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M8 3.33333V12.6667M3.33333 8H12.6667"
                                    stroke="currentColor"
                                    strokeWidth="1.5"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                />
                            </svg>
                            Add New
                        </button>
                    </div>

                    {/* Predefined Leave Types */}
                    <div className="rounded-xl border border-gray-200 overflow-hidden mb-8">
                        {/* Header */}
                        <div className="px-6 pt-6 pb-4">
                            <h3 className="text-xl font-semibold text-gray-900 m-0 text-center">
                                Predefined Leave
                            </h3>
                            <p className="text-sm text-gray-500 m-0 mt-1 text-center">
                                Select the leave types to enable for your organization.
                            </p>
                            <div className="border-t border-gray-200 mt-4"></div>
                        </div>

                        {/* Leave Type Grid */}
                        <div className="p-6">
                            {loadingTypes ? (
                                <div className="text-center py-6 text-gray-400 text-sm">
                                    Loading leave types...
                                </div>
                            ) : (
                                <div className="grid grid-cols-3 gap-y-6 gap-x-2">
                                    {leaveTypes.map(type => {
                                        const checked = formData.selectedLeaveTypes.includes(
                                            type.id
                                        );
                                        return (
                                            <label
                                                key={type.id}
                                                className="flex items-center gap-2 cursor-pointer select-none"
                                                onClick={() =>
                                                    toggleLeaveType(type.id)
                                                }
                                            >
                                                <span
                                                    className="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center transition-all duration-150"
                                                    style={{
                                                        border: checked
                                                            ? "none"
                                                            : "2px solid #D1D5DB",
                                                        backgroundColor: checked
                                                            ? "#14B8A6"
                                                            : "transparent"
                                                    }}
                                                >
                                                    {checked && (
                                                        <svg
                                                            width="10"
                                                            height="8"
                                                            viewBox="0 0 10 8"
                                                            fill="none"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                        >
                                                            <path
                                                                d="M9 1L3.5 6.5L1 4"
                                                                stroke="white"
                                                                strokeWidth="1.5"
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                            />
                                                        </svg>
                                                    )}
                                                </span>
                                                <span className="text-sm text-gray-700">
                                                    {type.name}
                                                </span>
                                            </label>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="mt-btn text-center">
                        <button
                            type="submit"
                            className="btn-primary no-underline"
                        >
                            Next
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default LeaveStep;
